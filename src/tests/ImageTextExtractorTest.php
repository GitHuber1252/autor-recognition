<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../app/Service/ImageTextExtractor.php';

class ImageTextExtractorTest extends TestCase
{
    public function testExtractTextFromImage()
    {
        $service = new ImageTextExtractor();

        $result = $service->extract("test.jpg");

        $this->assertIsString($result);
    }
}

