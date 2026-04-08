<?php

require_once __DIR__ . '/../Service/ImageTextExtractor.php';

class ResultController
{
    private string $apiUrl = 'http://python-ai:8000/predict';

    public function show(): array
    {
        // Получаем параметры из GET (как из /result, так и из /result.php)
        $file = $_GET['file'] ?? '';
        $fio = $_GET['fio'] ?? '';      // <-- добавляем получение ФИО
        $safeFile = basename($file);

        if ($safeFile === '') {
            return [
                'title' => 'Результат проверки',
                'probability' => null,
                'error' => 'Файл не указан.',
                'extractedText' => null,
                'fio' => $fio,           // <-- передаём даже пустое
            ];
        }

        $filePath = __DIR__ . '/../../public/uploads/' . $safeFile;
        if (!file_exists($filePath)) {
            return [
                'title' => 'Результат проверки',
                'probability' => null,
                'error' => 'Файл не найден на сервере.',
                'extractedText' => null,
                'fio' => $fio,
            ];
        }

        
        $probability = $this->callHandwritingApi($filePath)* 100;
        
        // Распознавание текста (опционально)
        $extractedText = null;
        try {
            $service = new ImageTextExtractor();
            $extractedText = $service->extract($filePath);
        } catch (Exception $e) {
            $extractedText = 'Ошибка распознавания текста: ' . $e->getMessage();
        }

        return [
            'title' => 'Результат проверки',
            'probability' => $probability,
            'error' => null,
            'extractedText' => $extractedText,
            'fio' => $fio,               
        ];
    }

    private function callHandwritingApi(string $filePath): ?float
    {
        if (!extension_loaded('curl')) {
            return null;
        }

        $postData = [
            'image' => new CURLFile($filePath, mime_content_type($filePath), basename($filePath))
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->apiUrl,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error || $httpCode !== 200) {
            error_log("FastAPI error: $error, HTTP $httpCode, response: $response");
            return null;
        }

        $data = json_decode($response, true);
        if (isset($data['chance']) && is_numeric($data['chance'])) {
            
            return (float) $data['chance'];
        }

        return null;
    }
}