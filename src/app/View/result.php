<?php
$title = $data['title'] ?? 'Результат проверки';
$probability = $data['probability'] ?? null;
$fio = $data['fio'] ?? null;
$error = $data['error'] ?? null;
$extractedText = $data['extractedText'] ?? null;
$file = $data['file'] ?? null;
$bestEtalon = $data['bestEtalon'] ?? null;
$bestEtalonFio = $data['bestEtalonFio'] ?? null;
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

        .uploaded-image {
            margin: 1rem auto 1.5rem;
            max-width: 100%;
            max-height: 320px;
            border-radius: 12px;
            border: 1px solid #333;
            display: block;
            object-fit: contain;
            background: #1a1a1a;
            padding: 4px;
        }

        .compare-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
            margin: 1rem 0 1.5rem;
            text-align: left;
        }

        .compare-card {
            background-color: #1a1a1a;
            border: 1px solid #333;
            border-radius: 12px;
            padding: 10px;
        }

        .compare-card h3 {
            margin: 0 0 8px 0;
            font-size: 1rem;
            color: #f0c36d;
        }

        .compare-card .meta {
            color: #bbbbbb;
            font-size: 0.9rem;
            margin: 6px 0 0 0;
            word-break: break-word;
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
            <?php if (!empty($file) || !empty($bestEtalon)): ?>
                <div class="compare-grid">
                    <?php if (!empty($file)): ?>
                        <div class="compare-card">
                            <h3>Фото для проверки</h3>
                            <img class="uploaded-image" src="/image.php?kind=probe&file=<?php echo urlencode((string) $file); ?>" alt="Фото для проверки">
                            <div class="meta">Введенное ФИО: <?php echo htmlspecialchars((string) $fio); ?></div>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($bestEtalon)): ?>
                        <div class="compare-card">
                            <h3>Лучшее совпадение из эталонов</h3>
                            <img class="uploaded-image" src="/image.php?kind=etalon&file=<?php echo urlencode((string) $bestEtalon); ?>" alt="Эталон">
                            <div class="meta">ФИО из эталона: <?php echo htmlspecialchars((string) $bestEtalonFio); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <p><strong>ВЕРОЯТНОСТЬ АВТОРСТВА:</strong></p>
            <div class="probability">
                <?php 
                    // Форматируем вероятность (например, 95.26%)
                    echo round($probability, 2) . '%'; 
                ?>
            </div>

            <?php if (!empty($bestEtalonFio)): ?>
                <p><strong>Наиболее вероятный автор по эталону:</strong><br>
                <small><?php echo htmlspecialchars((string) $bestEtalonFio); ?></small></p>
            <?php endif; ?>

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