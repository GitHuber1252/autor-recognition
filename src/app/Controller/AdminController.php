<?php

require_once __DIR__ . '/../Service/AIInferenceService.php';
require_once __DIR__ . '/../Service/FileUploadService.php';

class AdminController
{
    public function index(): array
    {
        $storage = new FileUploadService();
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
                    $result = $storage->saveUploadedFile($_FILES['etalon'], $fio, 'etalon');

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
                    $deleted = $storage->deleteFile('etalon', $filename);
                    if ($deleted) {
                        $message = 'Эталон удален.';
                    } else {
                        $error = 'Ошибка удаления эталона.';
                    }
                }
            }
        }

        $items = $storage->listFiles('etalon');

        return [
            'message' => $message,
            'error' => $error,
            'items' => $items,
        ];
    }
}
