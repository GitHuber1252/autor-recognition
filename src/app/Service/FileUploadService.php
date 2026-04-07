<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Ramsey\Uuid\Uuid;

class FileUploadService
{
    private string $uploadDir;

    public function __construct(string $uploadDir)
    {
        $this->uploadDir = rtrim($uploadDir, DIRECTORY_SEPARATOR);
    }

    public function saveUploadedFile(array $file): array
    {
        $extension = pathinfo($file['name'] ?? '', PATHINFO_EXTENSION);
        $uuid = Uuid::uuid4()->toString();
        $filename = $uuid . ($extension !== '' ? '.' . $extension : '');

        if (!is_dir($this->uploadDir) && !mkdir($this->uploadDir, 0777, true) && !is_dir($this->uploadDir)) {
            return ['success' => false, 'uuid' => null, 'filename' => null, 'path' => null];
        }

        $destination = $this->uploadDir . DIRECTORY_SEPARATOR . $filename;
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => false, 'uuid' => null, 'filename' => null, 'path' => null];
        }

        return ['success' => true, 'uuid' => $uuid, 'filename' => $filename, 'path' => $destination];
    }
}
