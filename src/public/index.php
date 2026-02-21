<?php

require __DIR__ . '/../vendor/autoload.php';

use Ramsey\Uuid\Uuid;

phpinfo();

echo '<pre>';
//print_r($_SERVER);
echo '</pre>';


$uuid =Uuid::uuid4();

echo $uuid;
echo "111111";