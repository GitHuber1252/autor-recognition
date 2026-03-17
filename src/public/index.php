<?php
// Загрузка автозагрузчика Composer и генерация UUID при необходимости
require __DIR__ . '/../vendor/autoload.php'; // Путь может отличаться, подстройте под ваш проект

use Ramsey\Uuid\Uuid;

// Обработка отправки формы
$uploadResult = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    $fio = $_POST['fio'] ?? '';
    $file = $_FILES['document'];

    // Простейшая валидация
    if (empty($fio)) {
        $uploadResult = '<p style="color: red;">Введите ФИО предполагаемого автора.</p>';
    } elseif ($file['error'] !== UPLOAD_ERR_OK) {
        $uploadResult = '<p style="color: red;">Ошибка загрузки файла. Попробуйте снова.</p>';
    } else {
        // Генерируем уникальный идентификатор для файла
        $uuid = Uuid::uuid4()->toString();
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFilename = $uuid . '.' . $extension;
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $destination = $uploadDir . $newFilename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $uploadResult = '<p style="color: #4caf50;">Файл успешно загружен. UUID: ' . $uuid . '</p>';
        } else {
            $uploadResult = '<p style="color: red;">Не удалось сохранить файл.</p>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Кто ты такое? — Определение автора рукописного текста</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #0b0b0b;
            color: #ffffff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
            box-sizing: border-box;
        }

        .main-card {
            max-width: 800px;
            width: 100%;
            background-color: #111111;
            border: 2px solid #333333;
            border-radius: 30px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.7);
        }

        h1 {
            font-size: 48px;
            font-weight: bold;
            color: #f0c36d;
            text-align: center;
            margin: 0 0 10px 0;
            letter-spacing: 2px;
        }

        .subheader {
            text-align: center;
            font-size: 24px;
            color: #cccccc;
            margin-bottom: 40px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }

        .block {
            margin-bottom: 30px;
        }

        .block-title {
            font-size: 22px;
            font-weight: bold;
            color: #f0c36d;
            margin-bottom: 10px;
        }

        .block-text {
            font-size: 16px;
            line-height: 1.5;
            color: #bbbbbb;
            background-color: #1a1a1a;
            padding: 15px;
            border-radius: 12px;
            border: 1px solid #333;
        }

        .upload-area {
            background-color: #1a1a1a;
            border: 2px dashed #555555;
            border-radius: 20px;
            padding: 40px 20px;
            text-align: center;
            color: #aaaaaa;
            margin: 30px 0 20px;
            transition: border-color 0.3s;
        }

        .upload-area:hover {
            border-color: #f0c36d;
        }

        .upload-area input[type="file"] {
            display: none;
        }

        .upload-label {
            display: inline-block;
            background-color: #333;
            color: white;
            padding: 12px 30px;
            border-radius: 30px;
            font-weight: bold;
            cursor: pointer;
            margin: 15px 0 5px;
            border: 1px solid #555;
        }

        .upload-label:hover {
            background-color: #444;
        }

        .input-field {
            width: 100%;
            padding: 15px 20px;
            background-color: #1a1a1a;
            border: 2px solid #444;
            border-radius: 50px;
            color: white;
            font-size: 16px;
            box-sizing: border-box;
            margin: 10px 0 20px;
        }

        .input-field:focus {
            outline: none;
            border-color: #f0c36d;
        }

        .btn {
            background-color: #f0c36d;
            color: #0b0b0b;
            font-weight: bold;
            font-size: 18px;
            padding: 15px 40px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            display: block;
            width: fit-content;
            margin: 20px auto 0;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #e0b25d;
        }

        .small-note {
            text-align: center;
            color: #777;
            font-size: 14px;
            margin-top: 30px;
        }

        .result-message {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="main-card">
    <h1>КТО ТЫ ТАКОЕ?</h1>
    <div class="subheader">ДОБРО ПОЖАЛОВАТЬ НА САЙТ</div>

    <div class="block">
        <div class="block-text">
            Сервис “Кто ты такое?” создан для определения автора рукописного текста по фотографии или отсканированной копии.
        </div>
    </div>

    <div class="block">
        <div class="block-title">КАК ЭТО РАБОТАЕТ?</div>
        <div class="block-text">
            Сервис работает на специально созданной и обученной нейросети и сравнивает текст с другими текстами в базе данных сервиса.
        </div>
    </div>

    <div class="block">
        <div class="block-title">КАК ПОЛЬЗОВАТЬСЯ СЕРВИСОМ?</div>
        <div class="block-text">
            Сервис доступен абсолютно бесплатно и не требует регистрации.
        </div>
    </div>

    <?php if (!empty($uploadResult)): ?>
        <div class="result-message"><?php echo $uploadResult; ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <div class="upload-area" onclick="document.getElementById('fileInput').click();">
            <div>Перетащите файл или выберите в проводнике</div>
            <input type="file" id="fileInput" name="document" accept="image/*, .pdf" style="display: none;" />
            <label for="fileInput" class="upload-label">Выбрать файл</label>
            <div style="margin-top: 8px; font-size: 14px;">Поддерживаются изображения и PDF</div>
        </div>

        <input type="text" class="input-field" name="fio" placeholder="Введите ФИО для проверки авторства" />

        <button type="submit" class="btn">Проверить</button>
    </form>

    <div class="small-note">
        * Результат появится на этой странице после обработки
    </div>
</div>

<!-- Простой скрипт для отображения имени выбранного файла (по желанию) -->
<script>
    document.getElementById('fileInput').addEventListener('change', function(e) {
        const fileName = e.target.files[0] ? e.target.files[0].name : '';
        if (fileName) {
            const label = document.querySelector('.upload-area div:first-child');
            label.textContent = 'Выбран файл: ' + fileName;
        }
    });
</script>
</body>
</html>
<?php

require __DIR__ . '/../vendor/autoload.php';






echo '<pre>';
//print_r($_SERVER);
echo '</pre>';




$uuid =Uuid::uuid4();

echo $uuid;
