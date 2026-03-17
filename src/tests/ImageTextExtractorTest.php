<?php
use PHPUnit\Framework\TestCase;

class ImageTextExtractorTest extends TestCase
{
    public function testExtractTextFromImage()
    {
        $service = new ImageTextExtractor();

        $result = $service->extract("test.jpg");

        $this->assertIsString($result);
    }
}