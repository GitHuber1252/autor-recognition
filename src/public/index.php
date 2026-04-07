<?php
require_once __DIR__ . '/../app/Controller/HomeController.php';

$controller = new HomeController();
$data = $controller->index();

require __DIR__ . '/../app/View/home.php';
