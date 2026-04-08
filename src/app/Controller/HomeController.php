<?php

require_once __DIR__ . '/../Service/FileUploadService.php';

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

        // Вместо вызова AI – редирект на страницу результата
        $safeFileName = basename($result['path']);
        header('Location: /result.php?file=' . urlencode($safeFileName) . '&fio=' . urlencode($fio));
        exit;
    }
}