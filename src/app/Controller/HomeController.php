<?php

require_once __DIR__ . '/../Service/FileUploadService.php';
require_once __DIR__ . '/../Service/AIInferenceService.php';

class HomeController
{
    public function index(): array
    {
        $uploadResult = '';
        $items = [];
        $galleryError = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
            $fio = trim($_POST['fio'] ?? '');
            $uploadResult = $this->handleUpload($fio, $_FILES['document']);
        }

        $storage = new FileUploadService();
        $items = $storage->listFiles('etalon');

        return [
            'uploadResult' => $uploadResult,
            'items' => $items,
            'galleryError' => $galleryError,
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

        $service = new FileUploadService();
        $result = $service->saveUploadedFile($file, $fio, 'probe');

        if (!$result['success']) {
            return '<p style="color: red;">Не удалось сохранить файл.</p>';
        }

        $filename = (string) ($result['filename'] ?? '');
        if ($filename === '') {
            return '<p style="color: red;">Ошибка сохранения имени файла.</p>';
        }

        header('Location: /result.php?file=' . urlencode($filename) . '&fio=' . urlencode($fio));
        exit;
    }

    private function aiUrl(): string
    {
        return getenv('AI_API_URL')
            ?: (getenv('AI_INTERNAL_URL') ?: 'http://127.0.0.1:8000/predict');
    }
}