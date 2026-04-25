<?php

require_once __DIR__ . '/../app/Service/FileUploadService.php';

$kind = trim((string) ($_GET['kind'] ?? ''));
$filename = trim((string) ($_GET['file'] ?? ''));

$kind = $kind === 'etalon' ? 'etalon' : 'probe';
$safe = basename($filename);

if ($safe === '' || $safe === '.' || $safe === '..') {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Image not found';
    exit;
}

$storage = new FileUploadService();
$path = $storage->getKindDir($kind) . DIRECTORY_SEPARATOR . $safe;
if (!is_file($path)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Image not found';
    exit;
}

$mimeType = function_exists('mime_content_type') ? (string) (mime_content_type($path) ?: 'application/octet-stream') : 'application/octet-stream';
header('Content-Type: ' . $mimeType);
header('Cache-Control: public, max-age=300');
readfile($path);
