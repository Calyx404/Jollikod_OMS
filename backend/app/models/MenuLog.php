<?php

class MenuLog extends BaseModel
{
    protected string $table = 'menu_logs';

    public function createLog(int $menu_item_id, ?int $staff_id, string $description): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} (menu_item_id, staff_id, description)
            VALUES (:menu_item_id, :staff_id, :description)
        ");
        $stmt->execute([
            ':menu_item_id' => $menu_item_id,
            ':staff_id' => $staff_id,
            ':description' => $description
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function listByMenu(int $menu_id): array
    {
        // menu_id -> find menu_items for menu then logs
        $stmt = $this->db->prepare("
            SELECT ml.*, mi.name as menu_item_name, s.name as staff_name
            FROM {$this->table} ml
            JOIN menu_items mi ON mi.menu_item_id = ml.menu_item_id
            LEFT JOIN staff s ON s.staff_id = ml.staff_id
            WHERE mi.menu_id = :menu_id
            ORDER BY ml.created_at DESC
        ");
        $stmt->execute([':menu_id' => $menu_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLogsByBranch(int $branch_id, string $from, string $to): array
    {
        $stmt = $this->db->prepare("
            SELECT ml.menu_log_id, mi.name AS item_name, s.name AS staff_name, ml.description, ml.created_at
            FROM menu_logs ml
            JOIN menu_items mi ON mi.menu_item_id = ml.menu_item_id
            LEFT JOIN staff s ON s.staff_id = ml.staff_id
            JOIN menus m ON m.menu_id = mi.menu_id
            WHERE m.branch_id = :branch_id
            AND DATE(ml.created_at) BETWEEN :from AND :to
            ORDER BY ml.created_at ASC
        ");

        $stmt->execute([
            ':branch_id' => $branch_id,
            ':from' => $from,
            ':to' => $to
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
