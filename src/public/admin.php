<?php
require_once __DIR__ . '/../app/Controller/AdminController.php';

$controller = new AdminController();
$data = $controller->index();

require __DIR__ . '/../app/View/admin.php';
