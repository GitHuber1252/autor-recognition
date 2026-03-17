<?php
require_once '../app/Service/ImageTextExtractor.php';

$service = new ImageTextExtractor();

$file = $_GET['file'];
$text = $service->extract('/var/www/uploads/' . $file);

echo "<h1>Result:</h1>";
echo "<p>$text</p>";