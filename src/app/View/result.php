<?php
$title = $data['title'] ?? 'Result';
$text = $data['text'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($title); ?></title>
</head>
<body>
    <h1><?php echo htmlspecialchars($title); ?>:</h1>
    <p><?php echo htmlspecialchars($text); ?></p>
</body>
</html>
