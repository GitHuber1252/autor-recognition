<?php

require_once __DIR__ . '/../Service/FileUploadService.php';
require_once __DIR__ . '/../Service/AIInferenceService.php';

class HomeController
{
    public function index(): array
    {
        $uploadResult = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
            $fio = trim($_POST['fio'] ?? '');
            $uploadResult = $this->handleUpload($fio, $_FILES['document']);
        }

        return [
            'uploadResult' => $uploadResult,
        ];
    }

    private function handleUpload(string $fio, array $file): string
    {
        if ($fio === '') {
            return '<p style="color: red;">Введите ФИО предполагаемого автора.</p>';
        }

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return '<p style="color: red;">Ошибка загрузки файла. Попробуйте снова.</p>';
        }

        $service = new FileUploadService(__DIR__ . '/../../public/uploads');
        $result = $service->saveUploadedFile($file);

        if (!$result['success']) {
            return '<p style="color: red;">Не удалось сохранить файл.</p>';
        }

        $aiUrl = getenv('AI_API_URL') ?: 'http://python-ai:8000/predict';
        $ai = new AIInferenceService($aiUrl);
        $prediction = $ai->predictChance($result['path']);

        if (!$prediction['success']) {
            return '<p style="color: red;">Ошибка AI: ' . htmlspecialchars($prediction['error']) . '</p>';
        }

        return '<p style="color: #4caf50;">Chance: ' . number_format($prediction['chance'] * 100, 2) . '%</p>';
    }
}
