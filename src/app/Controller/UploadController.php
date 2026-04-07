<?php
require_once __DIR__ . '/../Service/FileUploadService.php';

class UploadController
{
    public function upload()
    {
        $file = $_FILES['image'] ?? null;
        if ($file === null) {
            header('Location: /');
            return;
        }

        $service = new FileUploadService('/var/www/uploads');
        $result = $service->saveUploadedFile($file);

        if (!$result['success']) {
            header('Location: /');
            return;
        }

        header('Location: /result.php?file=' . urlencode($result['filename']));
    }
}