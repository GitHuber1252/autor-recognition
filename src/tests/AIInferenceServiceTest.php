<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../app/Service/AIInferenceService.php';

class FakeAIInferenceService extends AIInferenceService
{
    public array $jsonResponse = ['success' => true, 'error' => null, 'data' => []];
    public array $multipartResponse = ['success' => true, 'error' => null];

    protected function requestJson(string $method, string $url): array
    {
        return $this->jsonResponse;
    }

    protected function requestMultipart(string $method, string $url, array $payload): array
    {
        return $this->multipartResponse;
    }
}

class AIInferenceServiceTest extends TestCase
{
    public function testListEtalonsReturnsItems(): void
    {
        $service = new FakeAIInferenceService('http://python-ai:8000/predict');
        $service->jsonResponse = [
            'success' => true,
            'error' => null,
            'data' => ['items' => ['a.jpg', 'b.png']],
        ];

        $result = $service->listEtalons();

        $this->assertTrue($result['success']);
        $this->assertSame(['a.jpg', 'b.png'], $result['items']);
    }

    public function testListEtalonsHandlesInvalidFormat(): void
    {
        $service = new FakeAIInferenceService('http://python-ai:8000/predict');
        $service->jsonResponse = [
            'success' => true,
            'error' => null,
            'data' => ['items' => 'bad'],
        ];

        $result = $service->listEtalons();

        $this->assertFalse($result['success']);
        $this->assertSame([], $result['items']);
    }
}
