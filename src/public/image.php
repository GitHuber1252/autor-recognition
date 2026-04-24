<?php

require_once __DIR__ . '/../app/Repository/ImageRepository.php';

$repo = new ImageRepository();
$image = null;

$id = trim((string) ($_GET['id'] ?? ''));
$filename = trim((string) ($_GET['filename'] ?? ''));

if ($id !== '') {
    $image = $repo->getById($id);
} elseif ($filename !== '') {
    $image = $repo->getByStorageFilename($filename);
}

if ($image === null) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Image not found';
    exit;
}

$raw = $image['content'] ?? null;
$content = null;
if (is_string($raw)) {
    $content = $raw;
} elseif (is_resource($raw)) {
    $streamData = stream_get_contents($raw);
    if (is_string($streamData)) {
        $content = $streamData;
    }
}

if (!is_string($content) || $content === '') {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Image content is empty';
    exit;
}

$mimeType = (string) ($image['mime_type'] ?? 'application/octet-stream');
header('Content-Type: ' . $mimeType);
header('Cache-Control: public, max-age=300');
echo $content;
