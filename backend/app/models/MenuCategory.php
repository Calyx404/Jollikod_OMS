<?php

class MenuCategory extends BaseModel
{
    protected string $table = 'menu_categories';

    public function create(string $name): int
    {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (name) VALUES (:name)");
        $stmt->execute([':name' => $name]);
        return (int)$this->db->lastInsertId();
    }

    public function all(): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
