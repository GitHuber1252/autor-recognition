<?php

class AIInferenceService
{
    private string $endpoint;
    private string $baseUrl;
    private array $predictEndpoints;

    public function __construct(string $endpoint)
    {
        $this->endpoint = $endpoint;
        $this->baseUrl = preg_replace('#/predict$#', '', $endpoint) ?: $endpoint;
        $this->predictEndpoints = $this->buildPredictEndpoints($endpoint);
    }

    public function predictChance(string $filePath): array
    {
        if (!function_exists('curl_init')) {
            return ['success' => false, 'chance' => null, 'error' => 'cURL extension is not enabled in PHP'];
        }

        if (!is_file($filePath)) {
            return ['success' => false, 'chance' => null, 'error' => 'Uploaded file not found'];
        }

        $payload = ['image' => new CURLFile($filePath)];
        $lastError = 'AI request failed';

        foreach ($this->predictEndpoints as $endpoint) {
            // Retry helps when AI container is still warming up after restart.
            for ($attempt = 1; $attempt <= 3; $attempt++) {
                $ch = curl_init($endpoint);
                curl_setopt_array($ch, [
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $payload,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_CONNECTTIMEOUT => 8,
                ]);

                $response = curl_exec($ch);
                $curlError = curl_error($ch);
                $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($response === false) {
                    $lastError = $curlError ?: 'AI request failed';
                    if ($attempt < 3) {
                        usleep(500000);
                        continue;
                    }
                    break;
                }

                $data = json_decode($response, true);
                if ($statusCode >= 500 && $attempt < 3) {
                    usleep(500000);
                    continue;
                }

                if ($statusCode >= 400) {
                    $msg = is_array($data) ? ($data['detail'] ?? 'AI service returned error') : 'AI service returned error';
                    return ['success' => false, 'chance' => null, 'error' => $msg];
                }

                if (!is_array($data) || !isset($data['chance']) || !is_numeric($data['chance'])) {
                    return ['success' => false, 'chance' => null, 'error' => 'AI response does not contain valid chance'];
                }

                return ['success' => true, 'chance' => (float) $data['chance'], 'error' => null];
            }
        }

        return ['success' => false, 'chance' => null, 'error' => $lastError];
    }

    private function buildPredictEndpoints(string $endpoint): array
    {
        $urls = [$endpoint];
        $parts = parse_url($endpoint);
        $host = $parts['host'] ?? '';
        if ($host !== 'python-ai') {
            return array_values(array_unique($urls));
        }

        $path = ($parts['path'] ?? '/predict');
        $scheme = $parts['scheme'] ?? 'http';
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';

        $urls[] = $scheme . '://host.docker.internal' . $port . $path . $query;
        $urls[] = $scheme . '://localhost' . $port . $path . $query;

        return array_values(array_unique($urls));
    }

    public function listEtalons(): array
    {
        $response = $this->requestJson('GET', $this->baseUrl . '/etalons');
        if (!$response['success']) {
            return ['success' => false, 'items' => [], 'error' => $response['error']];
        }
        $items = $response['data']['items'] ?? [];
        if (!is_array($items)) {
            return ['success' => false, 'items' => [], 'error' => 'Invalid AI etalon list format'];
        }
        return ['success' => true, 'items' => $items, 'error' => null];
    }

    public function uploadEtalon(string $filePath, string $originalName): array
    {
        $payload = [
            'file' => new CURLFile($filePath, mime_content_type($filePath) ?: 'application/octet-stream', $originalName),
        ];
        return $this->requestMultipart('POST', $this->baseUrl . '/etalons', $payload);
    }

    public function deleteEtalon(string $filename): array
    {
        return $this->requestMultipart('POST', $this->baseUrl . '/etalons/delete', ['filename' => $filename]);
    }

    protected function requestJson(string $method, string $url): array
    {
        if (!function_exists('curl_init')) {
            return ['success' => false, 'error' => 'cURL extension is not enabled in PHP', 'data' => null];
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);
        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($response === false) {
            return ['success' => false, 'error' => $curlError ?: 'AI request failed', 'data' => null];
        }
        $data = json_decode($response, true);
        if ($statusCode >= 400) {
            $msg = is_array($data) ? ($data['detail'] ?? 'AI service returned error') : 'AI service returned error';
            return ['success' => false, 'error' => $msg, 'data' => null];
        }
        return ['success' => true, 'error' => null, 'data' => $data];
    }

    protected function requestMultipart(string $method, string $url, array $payload): array
    {
        if (!function_exists('curl_init')) {
            return ['success' => false, 'error' => 'cURL extension is not enabled in PHP'];
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);
        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($response === false) {
            return ['success' => false, 'error' => $curlError ?: 'AI request failed'];
        }
        $data = json_decode($response, true);
        if ($statusCode >= 400) {
            $msg = is_array($data) ? ($data['detail'] ?? 'AI service returned error') : 'AI service returned error';
            return ['success' => false, 'error' => $msg];
        }
        return ['success' => true, 'error' => null];
    }
}
