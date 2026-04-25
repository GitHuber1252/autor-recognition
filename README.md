# Распознавание автора

Веб-приложение на PHP + Nginx с сервисом ИИ на Python (сиамская модель) для сравнения рукописного текста.

## Структура проекта

- `src/` - PHP-приложение (контроллеры, сервисы, представления, публичные точки входа)
- `ai/` - FastAPI-сервис ИИ
- `ai/model/` - файл модели (`handwriting_expert_epoch_5.pth`)
- `docker/` - конфигурация nginx/supervisor/entrypoint для Railway

Изображения эталонов и проверок хранятся в папке на диске (Railway Volume): `/data/etalons` и `/data/probes`.

## Локальный запуск (Docker Compose)

1. Поместите файл модели:
   - `ai/model/handwriting_expert_epoch_5.pth`
2. Запуск:

```bash
docker compose up --build
```

- Сайт: `http://localhost:8080`
- Админка: `http://localhost:8080/admin.php`

Остановка:

```bash
docker compose down
```

## Деплой на Railway

Нужно 1 сервис (Web + AI внутри одного контейнера) из `Dockerfile.railway` + Volume для хранения файлов.

### 1) App service (web+ai)

- New Service -> Deploy from GitHub Repo
- Root Directory: `.`
- Dockerfile Path: `Dockerfile.railway`

Переменные:

- `MODEL_PATH=/app/model/handwriting_expert_epoch_5.pth`
- `AI_THRESHOLD=0.3`
- (опционально) `DATA_DIR=/data` (по умолчанию уже `/data`)

### 2) Volume (обязательно)

Добавьте Railway Volume и смонтируйте в контейнер **в `/data`**.
Приложение само создаст папки:

- `/data/etalons`
- `/data/probes`

### 3) Model file

Файл модели добавьте как Volume/attached storage так, чтобы он оказался по пути из `MODEL_PATH`
(например `/app/model/handwriting_expert_epoch_5.pth`).

После деплоя откройте домен этого сервиса (это и есть сайт).

## Тесты

Docker:

```bash
docker compose -f docker-compose.test.yaml up --build --abort-on-container-exit
```

Локально:

```bash
vendor/bin/phpunit -c phpunit.xml
pip install -r ai/requirements.txt
python -m pytest ai/tests -q
```