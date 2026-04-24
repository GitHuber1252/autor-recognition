<?php

require_once __DIR__ . '/../Infrastructure/Database.php';

class ImageRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function save(
        string $id,
        string $kind,
        ?string $fullName,
        string $originalFilename,
        string $storageFilename,
        string $mimeType,
        string $extension,
        string $content
    ): void {
        $sql = 'INSERT INTO images (id, kind, full_name, original_filename, storage_filename, mime_type, extension, content)
                VALUES (:id, :kind, :full_name, :original_filename, :storage_filename, :mime_type, :extension, :content)';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':kind', $kind);
        $stmt->bindValue(':full_name', $fullName);
        $stmt->bindValue(':original_filename', $originalFilename);
        $stmt->bindValue(':storage_filename', $storageFilename);
        $stmt->bindValue(':mime_type', $mimeType);
        $stmt->bindValue(':extension', $extension);
        $stmt->bindValue(':content', $content, PDO::PARAM_LOB);
        $stmt->execute();
    }

    public function getById(string $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM images WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function getByStorageFilename(string $filename): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM images WHERE storage_filename = :filename LIMIT 1');
        $stmt->execute([':filename' => $filename]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function listEtalons(): array
    {
        $stmt = $this->db->query("SELECT * FROM images WHERE kind = 'etalon' ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public function deleteEtalonByStorageFilename(string $filename): bool
    {
        $stmt = $this->db->prepare("DELETE FROM images WHERE kind = 'etalon' AND storage_filename = :filename");
        $stmt->execute([':filename' => $filename]);
        return $stmt->rowCount() > 0;
    }
}
