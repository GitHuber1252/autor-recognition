<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../Repository/ImageRepository.php';

use Ramsey\Uuid\Uuid;

class FileUploadService
{
    private ImageRepository $repository;

    public function __construct(?string $uploadDir = null)
    {
        $this->repository = new ImageRepository();
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
        if ($mimeType === '' && function_exists('mime_content_type')) {
            $detected = mime_content_type($tmpPath);
            $mimeType = is_string($detected) && $detected !== '' ? $detected : 'application/octet-stream';
        }
        if ($mimeType === '') {
            $mimeType = 'application/octet-stream';
        }

        $safeFio = preg_replace('/[^a-zA-Zа-яА-Я0-9]+/u', '_', $fio);
        $safeFio = trim((string) $safeFio, '_');
        if ($safeFio === '') {
            $safeFio = 'unknown';
        }

        $filename = $safeFio . '_photo_' . $id . ($extension !== '' ? '.' . $extension : '');
        $content = @file_get_contents($tmpPath);
        if (!is_string($content) || $content === '') {
            return ['success' => false, 'uuid' => null, 'filename' => null, 'path' => null, 'id' => null];
        }

        try {
            $this->repository->save(
                $id,
                $kind,
                $fio,
                $originalName,
                $filename,
                $mimeType,
                $extension,
                $content
            );
        } catch (Throwable $e) {
            return [
                'success' => false,
                'uuid' => null,
                'filename' => null,
                'path' => null,
                'id' => null,
                'error' => $e->getMessage(),
            ];
        }

        return [
            'success' => true,
            'uuid' => $id,
            'id' => $id,
            'filename' => $filename,
            'path' => null,
            'mime_type' => $mimeType,
        ];
    }
}
