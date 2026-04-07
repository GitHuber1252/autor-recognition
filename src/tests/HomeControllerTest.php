<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../app/Controller/HomeController.php';

class HomeControllerTest extends TestCase
{
    public function testIndexReturnsDefaultUploadResultOnGet(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        unset($_FILES['document']);
        unset($_POST['fio']);

        $controller = new HomeController();
        $result = $controller->index();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('uploadResult', $result);
        $this->assertSame('', $result['uploadResult']);
    }
}
