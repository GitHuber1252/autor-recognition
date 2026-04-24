<?php

require_once __DIR__ . '/../Infrastructure/Database.php';

class ComparisonRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function save(
        string $id,
        string $probeImageId,
        ?string $bestEtalonImageId,
        string $enteredFio,
        ?float $probability
    ): void {
        $sql = 'INSERT INTO comparisons (id, probe_image_id, best_etalon_image_id, entered_fio, match_probability)
                VALUES (:id, :probe_image_id, :best_etalon_image_id, :entered_fio, :match_probability)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':probe_image_id' => $probeImageId,
            ':best_etalon_image_id' => $bestEtalonImageId,
            ':entered_fio' => $enteredFio,
            ':match_probability' => $probability,
        ]);
    }
}
