<?php

class Menu extends BaseModel
{
    protected string $table = 'menus';

    public function createMenu(int $branch_id): int
    {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (branch_id) VALUES (:branch_id)");
        $stmt->execute([':branch_id' => $branch_id]);
        return (int)$this->db->lastInsertId();
    }

    public function getMenusByBranch(int $branch_id): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE branch_id = :branch_id");
        $stmt->execute([':branch_id' => $branch_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
