<?php
$title = $data['title'] ?? 'Результат проверки';
$probability = $data['probability'] ?? null;
$fio = $data['fio'] ?? null;
$error = $data['error'] ?? null;
$extractedText = $data['extractedText'] ?? null;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($title); ?></title>
    <style>
        body {
            font-family: system-ui, 'Segoe UI', Roboto, sans-serif;
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
            background: #0b0b0b;
            color: #e0e0e0;
        }

        .result-card {
            background-color: #111111;
            border: 2px solid #333333;
            border-radius: 30px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
        }

        .probability {
            font-size: 4rem;
            font-weight: bold;
            color: #f0c36d;
            margin: 1rem 0;
        }

        .recommendation {
            background-color: #1a1a1a;
            border: 1px solid #333;
            border-radius: 12px;
            padding: 1rem;
            margin: 1.5rem 0;
            font-size: 0.9rem;
            color: #bbbbbb;
        }

        .back-btn {
            display: inline-block;
            margin-top: 1.5rem;
            padding: 0.6rem 1.2rem;
            background-color: #f0c36d;
            color: #0b0b0b;
            font-weight: bold;
            text-decoration: none;
            border-radius: 50px;
            transition: background-color 0.3s;
            border: none;
            cursor: pointer;
        }

        .back-btn:hover {
            background-color: #e0b25d;
        }

        .error {
            color: #f0c36d;
            background-color: #1a1a1a;
            border: 1px solid #f0c36d;
            padding: 1rem;
            border-radius: 12px;
        }

        .extracted-text {
            margin-top: 2rem;
            padding: 1rem;
            background-color: #1a1a1a;
            border: 1px solid #333;
            border-radius: 12px;
            text-align: left;
            font-family: monospace;
            font-size: 0.9rem;
            white-space: pre-wrap;
            color: #bbbbbb;
        }

        hr {
            margin: 1.5rem 0;
            border-color: #333;
        }

        /* Дополнительно для вывода ФИО */
        .fio-display {
            font-size: 1.2rem;
            color: #f0c36d;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="result-card">
        <h1><?php echo htmlspecialchars($title); ?></h1>

        <?php if ($error): ?>
            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php elseif ($probability !== null): ?>
            <p><strong>ФИО предполагаемого автора</strong><br>
            <small><?php 
                    // Форматируем вероятность (например, 95.26%)
                    echo $fio; 
                ?></small></p>
            
            <p><strong>ВЕРОЯТНОСТЬ АВТОРСТВА:</strong></p>
            <div class="probability">
                <?php 
                    // Форматируем вероятность (например, 95.26%)
                    echo round($probability, 2) . '%'; 
                ?>
            </div>

            <div class="recommendation">
                Рекомендуем присылать скан документа, а не его фото,<br>
                для получения наиболее точного результата. Также убедитесь,<br>
                что вы верно ввели ФИО предполагаемого автора.
            </div>
        <?php else: ?>
            <div class="error">
                Не удалось получить вероятность. Проверьте, что FastAPI-сервис запущен.
            </div>
        <?php endif; ?>

        <?php if ($extractedText): ?>
            <hr>
            <div class="extracted-text">
                <strong>Распознанный текст (дополнительно):</strong><br>
                <?php echo nl2br(htmlspecialchars($extractedText)); ?>
            </div>
        <?php endif; ?>

        <a href="/" class="back-btn">← Вернуться на заглавную</a>
    </div>
</body>
</html>