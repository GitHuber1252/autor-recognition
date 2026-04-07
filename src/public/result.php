<?php
require_once __DIR__ . '/../app/Controller/ResultController.php';

$controller = new ResultController();
$data = $controller->show();

require __DIR__ . '/../app/View/result.php';