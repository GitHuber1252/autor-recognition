<?php
$message = $data['message'] ?? '';
$error = $data['error'] ?? '';
$items = $data['items'] ?? [];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Admin - Etalons</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background: #111; color: #eee; }
        .card { max-width: 900px; margin: 0 auto; background: #1d1d1d; border: 1px solid #333; border-radius: 12px; padding: 20px; }
        h1, h2 { color: #f0c36d; }
        .msg { color: #4caf50; margin-bottom: 10px; }
        .err { color: #ff6b6b; margin-bottom: 10px; }
        .row { display: flex; gap: 10px; align-items: center; margin-bottom: 10px; }
        input[type=file], button { padding: 8px; }
        ul { padding-left: 20px; }
        a { color: #8ecbff; }
        form.inline { display: inline; margin-left: 10px; }
    </style>
</head>
<body>
<div class="card">
    <h1>Управление эталонами</h1>
    <div><a href="/">Вернуться на главную</a></div>
    <br>

    <?php if ($message !== ''): ?><div class="msg"><?php echo $message; ?></div><?php endif; ?>
    <?php if ($error !== ''): ?><div class="err"><?php echo $error; ?></div><?php endif; ?>

    <h2>Загрузить новый эталон</h2>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="upload_etalon">
        <div class="row">
            <input type="file" name="etalon" accept="image/*" required>
            <button type="submit">Загрузить</button>
        </div>
    </form>

    <h2>Список эталонов (<?php echo count($items); ?>)</h2>
    <?php if (count($items) === 0): ?>
        <div>Эталоны пока не загружены.</div>
    <?php else: ?>
        <ul>
            <?php foreach ($items as $item): ?>
                <li>
                    <?php echo htmlspecialchars((string) $item); ?>
                    <form class="inline" method="post">
                        <input type="hidden" name="action" value="delete_etalon">
                        <input type="hidden" name="filename" value="<?php echo htmlspecialchars((string) $item); ?>">
                        <button type="submit">Удалить</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
</body>
</html>
