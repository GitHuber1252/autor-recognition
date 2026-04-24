<?php

require_once __DIR__ . '/../Service/ImageTextExtractor.php';
require_once __DIR__ . '/../Service/AIInferenceService.php';
require_once __DIR__ . '/../Repository/ImageRepository.php';
require_once __DIR__ . '/../Repository/ComparisonRepository.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Ramsey\Uuid\Uuid;

class ResultController
{
    public function show(): array
    {
        $probeId = trim((string) ($_GET['probe_id'] ?? ''));
        $fio = trim((string) ($_GET['fio'] ?? ''));
        if ($probeId === '') {
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

        $imageRepo = new ImageRepository();
        $comparisonRepo = new ComparisonRepository();
        $probe = $imageRepo->getById($probeId);
        if ($probe === null || ($probe['kind'] ?? '') !== 'probe') {
            return [
                'title' => 'Результат проверки',
                'probability' => null,
                'error' => 'Загруженное изображение не найдено в базе.',
                'extractedText' => null,
                'fio' => $fio,
                'file' => null,
                'bestEtalon' => null,
                'bestEtalonFio' => null,
            ];
        }

        $ai = new AIInferenceService($this->aiUrl());
        $tmpPath = tempnam(sys_get_temp_dir(), 'probe_');
        if ($tmpPath === false) {
            return [
                'title' => 'Результат проверки',
                'probability' => null,
                'error' => 'Не удалось подготовить файл для сравнения.',
                'extractedText' => null,
                'fio' => $fio,
                'file' => $probe['id'],
                'bestEtalon' => null,
                'bestEtalonFio' => null,
            ];
        }
        $probeContent = $this->blobToString($probe['content'] ?? null);
        if ($probeContent === null) {
            return [
                'title' => 'Результат проверки',
                'probability' => null,
                'error' => 'Не удалось прочитать изображение из базы.',
                'extractedText' => null,
                'fio' => $fio,
                'file' => $probe['id'],
                'bestEtalon' => null,
                'bestEtalonFio' => null,
            ];
        }
        file_put_contents($tmpPath, $probeContent);
        $aiResult = $ai->predictChance($tmpPath);
        @unlink($tmpPath);

        if (!$aiResult['success']) {
            return [
                'title' => 'Результат проверки',
                'probability' => null,
                'error' => $aiResult['error'] ?? 'Ошибка сравнения с эталонами.',
                'extractedText' => null,
                'fio' => $fio !== '' ? $fio : ($probe['full_name'] ?? ''),
                'file' => $probe['id'],
                'bestEtalon' => null,
                'bestEtalonFio' => null,
            ];
        }

        $probability = ((float) ($aiResult['chance'] ?? 0)) * 100;
        $bestEtalon = isset($aiResult['best_etalon']) && is_string($aiResult['best_etalon']) ? $aiResult['best_etalon'] : null;
        $bestEtalonFio = isset($aiResult['best_etalon_person']) && is_string($aiResult['best_etalon_person']) && $aiResult['best_etalon_person'] !== ''
            ? $aiResult['best_etalon_person']
            : ($bestEtalon !== null ? $this->extractFioFromFilename($bestEtalon) : null);
        $bestEtalonId = isset($aiResult['best_etalon_id']) && is_string($aiResult['best_etalon_id']) ? $aiResult['best_etalon_id'] : null;
        
        $extractedText = null;
        try {
            $service = new ImageTextExtractor();
            $ocrTmpPath = tempnam(sys_get_temp_dir(), 'ocr_');
            if ($ocrTmpPath !== false) {
                file_put_contents($ocrTmpPath, $probeContent);
                $extractedText = $service->extract($ocrTmpPath);
                @unlink($ocrTmpPath);
            }
        } catch (Exception $e) {
            $extractedText = 'Ошибка распознавания текста: ' . $e->getMessage();
        }

        try {
            $comparisonRepo->save(
                Uuid::uuid4()->toString(),
                $probeId,
                $bestEtalonId,
                $fio !== '' ? $fio : (string) ($probe['full_name'] ?? ''),
                $probability
            );
        } catch (Throwable $e) {
            error_log('Failed to save comparison: ' . $e->getMessage());
        }

        return [
            'title' => 'Результат проверки',
            'probability' => $probability,
            'error' => null,
            'extractedText' => $extractedText,
            'fio' => $fio !== '' ? $fio : (string) ($probe['full_name'] ?? ''),
            'file' => $probe['id'],
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
        return getenv('AI_API_URL') ?: 'http://python-ai:8000/predict';
    }

    private function blobToString($value): ?string
    {
        if (is_string($value)) {
            return $value;
        }
        if (is_resource($value)) {
            $content = stream_get_contents($value);
            return is_string($content) ? $content : null;
        }
        return null;
    }
}