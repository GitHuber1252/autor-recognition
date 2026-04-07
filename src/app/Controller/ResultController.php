<?php

require_once __DIR__ . '/../Service/ImageTextExtractor.php';

class ResultController
{
    public function show(): array
    {
        $file = $_GET['file'] ?? '';
        $safeFile = basename($file);

        if ($safeFile === '') {
            return [
                'title' => 'Result',
                'text' => 'File is not specified.',
            ];
        }

        $service = new ImageTextExtractor();
        $text = $service->extract('/var/www/uploads/' . $safeFile);

        return [
            'title' => 'Result',
            'text' => $text,
        ];
    }
}
