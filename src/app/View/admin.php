<?php
$message = $data['message'] ?? '';
$error = $data['error'] ?? '';
$items = $data['items'] ?? [];

// Extract FIO from format: <fio>_photo_<uuid>.<ext>
function extractFio($filename) {
    $base = pathinfo($filename, PATHINFO_FILENAME);
    $marker = '_photo_';
    $pos = mb_strrpos($base, $marker);

    if ($pos !== false) {
        $fioPart = mb_substr($base, 0, $pos);
        $fioPart = str_replace('_', ' ', $fioPart);
        $fioPart = trim($fioPart);
        return $fioPart !== '' ? $fioPart : 'Неизвестно';
    }

    // Fallback for legacy names.
    $legacy = str_replace('_', ' ', $base);
    $legacy = trim($legacy);
    return $legacy !== '' ? $legacy : 'Неизвестно';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Admin - Etalons</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background: #111; color: #eee; }
        .card { max-width: 1000px; margin: 0 auto; background: #1d1d1d; border-radius: 12px; padding: 20px; }
        h1, h2 { color: #f0c36d; }

        .msg { color: #4caf50; margin-bottom: 10px; }
        .err { color: #ff6b6b; margin-bottom: 10px; }

        .row { display: flex; gap: 10px; align-items: center; margin-bottom: 10px; }

        input[type=file], input[type=text], button {
            padding: 8px;
            border-radius: 6px;
            border: none;
        }

        input[type=text] {
            width: 250px;
        }

        button {
            background: #f0c36d;
            cursor: pointer;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 15px;
        }

        .item {
            background: #222;
            padding: 10px;
            border-radius: 10px;
            text-align: center;
        }

        .item img {
            width: 100%;
            height: 140px;
            object-fit: cover;
            border-radius: 8px;
        }

        .fio {
            margin-top: 8px;
            font-size: 14px;
        }

        .filename {
            font-size: 11px;
            color: #aaa;
            word-break: break-all;
        }

        form.inline {
            margin-top: 5px;
        }
    </style>
</head>
<body>
<div class="card">
    <h1>Управление эталонами</h1>
    <div><a href="/">Вернуться на главную</a></div>
    <br>

    <?php if ($message !== ''): ?>
        <div class="msg"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="err"><?php echo $error; ?></div>
    <?php endif; ?>

    <h2>Загрузить новый эталон</h2>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="upload_etalon">
        <div class="row">
            <input type="text" name="fio" placeholder="ФИО" required>
            <input type="file" name="etalon" accept="image/*" required>
            <button type="submit">Загрузить</button>
        </div>
    </form>

    <h2>Список эталонов (<?php echo count($items); ?>)</h2>

    <?php if (count($items) === 0): ?>
        <div>Эталоны пока не загружены.</div>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($items as $item): ?>
                <?php
                    $filename = is_array($item) ? ($item['filename'] ?? '') : (string) $item;
                    $fullName = is_array($item) ? ($item['full_name'] ?? '') : '';
                    if ($filename === '') {
                        continue;
                    }
                ?>
                <div class="item">
                    <img src="/image.php?filename=<?php echo urlencode($filename); ?>" alt="">

                    <div class="fio">
                        <?php
                            $nameToShow = is_string($fullName) && trim($fullName) !== '' ? $fullName : extractFio($filename);
                            echo htmlspecialchars($nameToShow);
                        ?>
                    </div>

                    <div class="filename">
                        <?php echo htmlspecialchars($filename); ?>
                    </div>

                    <form class="inline" method="post">
                        <input type="hidden" name="action" value="delete_etalon">
                        <input type="hidden" name="filename" value="<?php echo htmlspecialchars($filename); ?>">
                        <button type="submit">Удалить</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>