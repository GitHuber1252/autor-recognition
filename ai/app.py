import io
import os

import cv2
import numpy as np
import torch
import torch.nn as nn
import torch.nn.functional as F
from fastapi import FastAPI, File, Form, HTTPException, UploadFile
from PIL import Image
from torchvision import transforms


class SiameseNetwork(nn.Module):
    def __init__(self) -> None:
        super().__init__()
        from torchvision import models

        self.resnet = models.resnet18(weights=None)
        num_features = self.resnet.fc.in_features
        self.resnet.fc = nn.Sequential(
            nn.Linear(num_features, 512),
            nn.ReLU(),
            nn.Dropout(0.5),
            nn.Linear(512, 256),
        )

    def forward_once(self, x: torch.Tensor) -> torch.Tensor:
        return self.resnet(x)

    def forward(self, x1: torch.Tensor, x2: torch.Tensor) -> tuple[torch.Tensor, torch.Tensor]:
        return self.forward_once(x1), self.forward_once(x2)


app = FastAPI(title="Handwriting AI", version="1.0.0")

MODEL_PATH = os.getenv("MODEL_PATH", "/app/model/handwriting_expert_epoch_5.pth")
ETALON_DIR = os.getenv("ETALON_DIR", "/app/etalons")
THRESHOLD = float(os.getenv("AI_THRESHOLD", "0.3"))
DEVICE = torch.device("cpu")

transform = transforms.Compose(
    [
        transforms.Resize((256, 256)),
        transforms.ToTensor(),
        transforms.Normalize(mean=[0.485, 0.456, 0.406], std=[0.229, 0.224, 0.225]),
    ]
)

model = SiameseNetwork().to(DEVICE)
model_loaded = False
model_load_error = ""


def _try_load_model() -> None:
    global model_loaded, model_load_error
    if not os.path.exists(MODEL_PATH):
        model_load_error = f"Model file not found: {MODEL_PATH}"
        return

    try:
        checkpoint = torch.load(MODEL_PATH, map_location=DEVICE)
        if isinstance(checkpoint, dict) and "state_dict" in checkpoint:
            checkpoint = checkpoint["state_dict"]

        model.load_state_dict(checkpoint, strict=True)
        model.eval()
        model_loaded = True
    except Exception as exc:
        model_load_error = str(exc)


def dist_to_prob(distance: float, threshold: float = 0.3) -> float:
    k = 10.0
    prob = 1.0 / (1.0 + np.exp(k * (distance - threshold)))
    return float(prob)   


def preprocess_image(img: Image.Image) -> Image.Image:
    img_np = np.array(img)
    gray = cv2.cvtColor(img_np, cv2.COLOR_RGB2GRAY)
    clahe = cv2.createCLAHE(clipLimit=3.0, tileGridSize=(16, 16))
    enhanced = clahe.apply(gray)
    enhanced_rgb = cv2.cvtColor(enhanced, cv2.COLOR_GRAY2RGB)
    return Image.fromarray(enhanced_rgb)


def transform_image(img: Image.Image) -> torch.Tensor:
    img_preprocessed = preprocess_image(img.convert("RGB"))
    return transform(img_preprocessed).unsqueeze(0).to(DEVICE)


def list_etalon_paths() -> list[str]:
    if not os.path.isdir(ETALON_DIR):
        return []
    allowed = {".jpg", ".jpeg", ".png", ".bmp", ".webp"}
    paths: list[str] = []
    for name in os.listdir(ETALON_DIR):
        full_path = os.path.join(ETALON_DIR, name)
        _, ext = os.path.splitext(name.lower())
        if os.path.isfile(full_path) and ext in allowed:
            paths.append(full_path)
    return sorted(paths)


def safe_etalon_filename(name: str) -> str:
    base = os.path.basename(name).strip()
    if base == "":
        raise HTTPException(status_code=400, detail="Empty filename")
    if base in {".", ".."}:
        raise HTTPException(status_code=400, detail="Invalid filename")
    return base


# ---- Вспомогательная функция для проверки, является ли файл изображением ----
def validate_image_file(contents: bytes) -> Image.Image:
    """
    Проверяет, можно ли открыть байты как изображение.
    Возвращает объект Image в режиме RGB или выбрасывает HTTPException.
    """
    try:
        img = Image.open(io.BytesIO(contents))
        # Принудительно конвертируем в RGB, чтобы отсечь неподдерживаемые режимы
        img = img.convert("RGB")
        return img
    except Exception as exc:
        raise HTTPException(status_code=400, detail=f"Invalid image file: {exc}")


@app.on_event("startup")
def startup_event() -> None:
    _try_load_model()

@app.get("/")
def root() -> dict:
    """Корневой эндпоинт – приветствие и ссылка на документацию."""
    return {
        "message": "Handwriting AI API is running.",
        "docs": "/docs",
        "health": "/health"
    }

@app.get("/health")
def health() -> dict:
    etalon_count = len(list_etalon_paths())
    return {"ok": model_loaded and etalon_count > 0, "error": model_load_error, "etalon_count": etalon_count}


@app.post("/predict")
async def predict(image: UploadFile = File(...)) -> dict:
    if not model_loaded:
        raise HTTPException(status_code=500, detail=f"Model is not loaded: {model_load_error}")

    etalon_paths = list_etalon_paths()
    if not etalon_paths:
        raise HTTPException(status_code=500, detail=f"No etalon images found in: {ETALON_DIR}")

    contents = await image.read()
    # Валидация и открытие изображения
    test_image = validate_image_file(contents)

    test_tensor = transform_image(test_image)

    with torch.no_grad():
        best_chance = 0.0
        for etalon_path in etalon_paths:
            etalon_image = Image.open(etalon_path).convert("RGB")
            etalon_tensor = transform_image(etalon_image)
            out1, out2 = model(etalon_tensor, test_tensor)
            distance = F.pairwise_distance(out1, out2).item()
            chance = dist_to_prob(distance, THRESHOLD)
            if chance > best_chance:
                best_chance = chance

    return {"chance": round(best_chance, 4)}


@app.get("/etalons")
def list_etalons() -> dict:
    etalon_paths = list_etalon_paths()
    names = [os.path.basename(path) for path in etalon_paths]
    return {"items": names, "count": len(names)}


@app.post("/etalons")
async def upload_etalon(file: UploadFile = File(...)) -> dict:
    os.makedirs(ETALON_DIR, exist_ok=True)
    filename = safe_etalon_filename(file.filename or "")
    destination = os.path.join(ETALON_DIR, filename)
    contents = await file.read()

    # Валидация изображения (если невалидно – выбросит исключение)
    validate_image_file(contents)

    with open(destination, "wb") as f:
        f.write(contents)

    return {"ok": True, "filename": filename}


@app.post("/etalons/delete")
def delete_etalon(filename: str = Form(...)) -> dict:
    safe_name = safe_etalon_filename(filename)
    target = os.path.join(ETALON_DIR, safe_name)
    if not os.path.exists(target):
        raise HTTPException(status_code=404, detail="Etalon file not found")
    os.remove(target)
    return {"ok": True, "filename": safe_name}