<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Ramsey\Uuid\Uuid;

class FileUploadService
{
    private string $baseDir;

    public function __construct(?string $baseDir = null)
    {
        $this->baseDir = rtrim($baseDir ?: (getenv('DATA_DIR') ?: '/data'), DIRECTORY_SEPARATOR);
    }

    public function saveUploadedFile(array $file, string $fio, string $kind = 'probe'): array
    {
        $tmpPath = $file['tmp_name'] ?? '';
        if (!is_string($tmpPath) || $tmpPath === '' || !is_file($tmpPath)) {
            return ['success' => false, 'uuid' => null, 'filename' => null, 'path' => null, 'id' => null];
        }

        $originalName = (string) ($file['name'] ?? 'upload.bin');
        $extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
        $id = Uuid::uuid4()->toString();
        $mimeType = (string) ($file['type'] ?? '');

        $safeFio = preg_replace('/[^a-zA-Zа-яА-Я0-9]+/u', '_', $fio);
        $safeFio = trim((string) $safeFio, '_');
        if ($safeFio === '') {
            $safeFio = 'unknown';
        }

        $filename = $safeFio . '_photo_' . $id . ($extension !== '' ? '.' . $extension : '');

        $targetDir = $this->getKindDir($kind);
        if (!is_dir($targetDir) && !mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
            return ['success' => false, 'uuid' => null, 'filename' => null, 'path' => null, 'id' => null];
        }

        $destination = $targetDir . DIRECTORY_SEPARATOR . $filename;
        if (!move_uploaded_file($tmpPath, $destination)) {
            return ['success' => false, 'uuid' => null, 'filename' => null, 'path' => null, 'id' => null];
        }

        return [
            'success' => true,
            'uuid' => $id,
            'filename' => $filename,
            'path' => $destination,
            'id' => $id,
        ];
    }

    public function getKindDir(string $kind): string
    {
        $safeKind = $kind === 'etalon' ? 'etalons' : 'probes';
        return $this->baseDir . DIRECTORY_SEPARATOR . $safeKind;
    }

    public function listFiles(string $kind): array
    {
        $dir = $this->getKindDir($kind);
        if (!is_dir($dir)) {
            return [];
        }
        $allowed = ['jpg', 'jpeg', 'png', 'bmp', 'webp'];
        $files = scandir($dir);
        if ($files === false) {
            return [];
        }
        $result = [];
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (!is_file($path)) {
                continue;
            }
            $ext = strtolower((string) pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, $allowed, true)) {
                $result[] = $file;
            }
        }
        sort($result);
        return $result;
    }

    public function deleteFile(string $kind, string $filename): bool
    {
        $safe = basename($filename);
        if ($safe === '' || $safe === '.' || $safe === '..') {
            return false;
        }
        $path = $this->getKindDir($kind) . DIRECTORY_SEPARATOR . $safe;
        return is_file($path) ? unlink($path) : false;
    }
}
