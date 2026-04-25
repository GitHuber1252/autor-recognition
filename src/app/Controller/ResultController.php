<?php

require_once __DIR__ . '/../Service/ImageTextExtractor.php';
require_once __DIR__ . '/../Service/AIInferenceService.php';
require_once __DIR__ . '/../Service/FileUploadService.php';
require_once __DIR__ . '/../../vendor/autoload.php';

class ResultController
{
    public function show(): array
    {
        $file = trim((string) ($_GET['file'] ?? ''));
        $fio = trim((string) ($_GET['fio'] ?? ''));
        $safeFile = basename($file);

        if ($safeFile === '') {
            return [
                'title' => 'Результат проверки',
                'probability' => null,
                'error' => 'Загруженный файл не указан.',
                'extractedText' => null,
                'fio' => $fio,
                'file' => null,
                'bestEtalon' => null,
                'bestEtalonFio' => null,
            ];
        }

        $storage = new FileUploadService();
        $probePath = $storage->getKindDir('probe') . DIRECTORY_SEPARATOR . $safeFile;
        if (!is_file($probePath)) {
            return [
                'title' => 'Результат проверки',
                'probability' => null,
                'error' => 'Загруженное изображение не найдено.',
                'extractedText' => null,
                'fio' => $fio,
                'file' => null,
                'bestEtalon' => null,
                'bestEtalonFio' => null,
            ];
        }

        $ai = new AIInferenceService($this->aiUrl());
        $aiResult = $ai->predictChance($probePath);

        if (!$aiResult['success']) {
            return [
                'title' => 'Результат проверки',
                'probability' => null,
                'error' => $aiResult['error'] ?? 'Ошибка сравнения с эталонами.',
                'extractedText' => null,
                'fio' => $fio,
                'file' => $safeFile,
                'bestEtalon' => null,
                'bestEtalonFio' => null,
            ];
        }

        $probability = ((float) ($aiResult['chance'] ?? 0)) * 100;
        $bestEtalon = isset($aiResult['best_etalon']) && is_string($aiResult['best_etalon']) ? $aiResult['best_etalon'] : null;
        $bestEtalonFio = isset($aiResult['best_etalon_person']) && is_string($aiResult['best_etalon_person']) && $aiResult['best_etalon_person'] !== ''
            ? $aiResult['best_etalon_person']
            : ($bestEtalon !== null ? $this->extractFioFromFilename($bestEtalon) : null);
        
        $extractedText = null;
        try {
            $service = new ImageTextExtractor();
            $extractedText = $service->extract($probePath);
        } catch (Exception $e) {
            $extractedText = 'Ошибка распознавания текста: ' . $e->getMessage();
        }

        return [
            'title' => 'Результат проверки',
            'probability' => $probability,
            'error' => null,
            'extractedText' => $extractedText,
            'fio' => $fio,
            'file' => $safeFile,
            'bestEtalon' => $bestEtalon,
            'bestEtalonFio' => $bestEtalonFio,
        ];
    }

    private function extractFioFromFilename(string $filename): string
    {
        $base = pathinfo($filename, PATHINFO_FILENAME);
        $marker = '_photo_';
        $pos = mb_strrpos($base, $marker);
        if ($pos !== false) {
            $fioPart = mb_substr($base, 0, $pos);
            $fioPart = trim(str_replace('_', ' ', $fioPart));
            if ($fioPart !== '') {
                return $fioPart;
            }
        }

        $fallback = trim(str_replace('_', ' ', $base));
        return $fallback !== '' ? $fallback : 'Неизвестно';
    }

    private function aiUrl(): string
    {
        return getenv('AI_API_URL')
            ?: (getenv('AI_INTERNAL_URL') ?: 'http://127.0.0.1:8000/predict');
    }
}