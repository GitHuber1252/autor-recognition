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

        $ai = new AIInferenceService($this->aiUrl());
        $etalons = $ai->listEtalons();
        if ($etalons['success']) {
            $items = $etalons['items'] ?? [];
        } else {
            $galleryError = 'Не удалось загрузить фото образцов.';
        }

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

        $probeId = $result['id'] ?? '';
        if (!is_string($probeId) || $probeId === '') {
            return '<p style="color: red;">Ошибка сохранения идентификатора загрузки.</p>';
        }

        header('Location: /result.php?probe_id=' . urlencode($probeId) . '&fio=' . urlencode($fio));
        exit;
    }

    private function aiUrl(): string
    {
        return getenv('AI_API_URL') ?: 'http://python-ai:8000/predict';
    }
}