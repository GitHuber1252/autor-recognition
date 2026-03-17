<?php
class UploadController
{
    public function upload()
    {
        $file = $_FILES['image'];

        $path = '/var/www/uploads/' . $file['name'];
        move_uploaded_file($file['tmp_name'], $path);

        header("Location: /result.php?file=" . $file['name']);
    }
}