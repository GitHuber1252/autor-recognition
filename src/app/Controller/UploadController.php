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

        $fio = trim($_POST['fio'] ?? 'unknown');
        $service = new FileUploadService();
        $result = $service->saveUploadedFile($file, $fio, 'probe');

        if (!$result['success']) {
            header('Location: /');
            return;
        }

        header('Location: /result.php?probe_id=' . urlencode((string) ($result['id'] ?? '')) . '&fio=' . urlencode($fio));
    }
}