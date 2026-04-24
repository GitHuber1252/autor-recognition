<?php

require_once __DIR__ . '/../Service/AIInferenceService.php';

class AdminController
{
    public function index(): array
    {
        $ai = new AIInferenceService($this->aiUrl());
        $message = '';
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'upload_etalon') {
                $fio = trim($_POST['fio'] ?? '');

                if ($fio === '') {
                    $error = 'Введите ФИО.';
                } elseif (!isset($_FILES['etalon']) || ($_FILES['etalon']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                    $error = 'Выберите файл эталона для загрузки.';
                } else {
                    $tmpPath = $_FILES['etalon']['tmp_name'];
                    $name = $_FILES['etalon']['name'] ?? 'etalon.jpg';

                    $result = $ai->uploadEtalon($tmpPath, $name, $fio);

                    if ($result['success']) {
                        $message = 'Эталон успешно загружен.';
                    } else {
                        $error = 'Ошибка загрузки эталона: ' . htmlspecialchars($result['error'] ?? 'Unknown error');
                    }
                }
            }

            if ($action === 'delete_etalon') {
                $filename = trim($_POST['filename'] ?? '');
                if ($filename === '') {
                    $error = 'Не выбран файл для удаления.';
                } else {
                    $result = $ai->deleteEtalon($filename);
                    if ($result['success']) {
                        $message = 'Эталон удален.';
                    } else {
                        $error = 'Ошибка удаления эталона: ' . htmlspecialchars($result['error'] ?? 'Unknown error');
                    }
                }
            }
        }

        $etalons = $ai->listEtalons();
        if (!$etalons['success']) {
            $error = $error !== '' ? $error : ('Ошибка получения списка эталонов: ' . htmlspecialchars($etalons['error'] ?? 'Unknown error'));
        }

        return [
            'message' => $message,
            'error' => $error,
            'items' => $etalons['items'] ?? [],
        ];
    }

    private function aiUrl(): string
    {
        return getenv('AI_API_URL') ?: 'http://python-ai:8000/predict';
    }
}
