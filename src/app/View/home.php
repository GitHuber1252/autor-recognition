<?php
$uploadResult = $data['uploadResult'] ?? '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Кто ты такое? - Определение автора рукописного текста</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #0b0b0b;
            color: #111010;
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
    <div style="text-align: center; margin-bottom: 20px;">
        <a href="/admin.php" style="color: #8ecbff;">Управление эталонами</a>
    </div>

    <div class="block">
        <div class="block-text">
            Сервис "Кто ты такое?" создан для определения автора рукописного текста по фотографии или отсканированной копии.
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
        <div class="upload-area">
            <div>Перетащите файл или выберите в проводнике</div>
            <input type="file" id="fileInput" name="document" accept="image/*, .pdf" />
            <label for="fileInput" class="upload-label">Выбрать файл</label>
            <div style="margin-top: 8px; font-size: 14px;">Поддерживаются изображения и PDF</div>
        </div>

        <input type="text" class="input-field" name="fio" placeholder="Введите ФИО для проверки авторства" />

        <button type="submit" class="btn">Проверить</button>
    </form>

    
</div>

<script>
    document.getElementById('fileInput').addEventListener('change', function (e) {
        const fileName = e.target.files[0] ? e.target.files[0].name : '';
        if (fileName) {
            const label = document.querySelector('.upload-area div:first-child');
            label.textContent = 'Выбран файл: ' + fileName;
        }
    });
</script>
</body>
</html>
