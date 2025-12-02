<?php

class MenuItem extends BaseModel
{
    protected string $table = 'menu_items';

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} 
            (menu_id, menu_category_id, name, description, price, image_path)
            VALUES 
            (:menu_id, :menu_category_id, :name, :description, :price, :image_path)
        ");

        $stmt->execute([
            ':menu_id' => $data['menu_id'],
            ':menu_category_id' => $data['menu_category_id'],
            ':name' => $data['name'],
            ':description' => $data['description'] ?? '',
            ':price' => $data['price'] ?? 0,
            ':image_path' => $data['image_path'] ?? ''
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function allByMenu(int $menu_id): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE menu_id = :menu_id");
        $stmt->execute([':menu_id' => $menu_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE menu_item_id = :id");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    // returns items for a branch (joins menus)
    public function getByBranch(int $branch_id): array
    {
        $stmt = $this->db->prepare("
            SELECT mi.*
            FROM menu_items mi
            JOIN menus m ON m.menu_id = mi.menu_id
            WHERE m.branch_id = :branch_id
            ORDER BY mi.name ASC
        ");
        $stmt->execute([':branch_id' => $branch_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPrice(int $menu_item_id): float
    {
        $stmt = $this->db->prepare("SELECT price FROM menu_items WHERE menu_item_id = :id LIMIT 1");
        $stmt->execute([':id' => $menu_item_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (float)$row['price'] : 0.0;
    }

    // Rankings: units sold and revenue for branch/time-range
    public function getRankings(int $branch_id, string $from, string $to, int $limit = 50): array
    {
        $stmt = $this->db->prepare("
            SELECT mi.menu_item_id, mi.name,
                SUM(oi.quantity) AS units_sold,
                SUM(oi.total) AS revenue
            FROM order_items oi
            JOIN orders o ON o.order_id = oi.order_id
            JOIN menu_items mi ON mi.menu_item_id = oi.menu_item_id
            JOIN menus m ON m.menu_id = mi.menu_id
            WHERE m.branch_id = :branch_id
            AND DATE(o.created_at) BETWEEN :from AND :to
            GROUP BY mi.menu_item_id
            ORDER BY units_sold DESC, revenue DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':branch_id', $branch_id, PDO::PARAM_INT);
        $stmt->bindValue(':from', $from);
        $stmt->bindValue(':to', $to);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAvailableByBranch(int $branch_id, ?string $datetimeIso = null): array
    {
        // Fetch all active items for the branch
        $stmt = $this->db->prepare("
            SELECT mi.* 
            FROM menu_items mi
            JOIN menus m ON m.menu_id = mi.menu_id
            WHERE m.branch_id = :branch_id
            AND mi.is_active = 1
            ORDER BY mi.name ASC
        ");
        $stmt->execute([':branch_id' => $branch_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // if datetime not provided, evaluate against current server time
        $dt = $datetimeIso ? new DateTime($datetimeIso) : new DateTime();

        // require schedule model
        $scheduleModel = new MenuItemSchedule();

        $available = [];
        foreach ($items as $item) {
            // if item has no schedules, treat as always available
            $schedules = $scheduleModel->listByMenuItem((int)$item['menu_item_id']);
            if (empty($schedules)) {
                $available[] = $item;
                continue;
            }

            // check if any schedule matches current datetime
            $matches = $scheduleModel->schedulesForDatetime((int)$item['menu_item_id'], $dt);
            if (!empty($matches)) {
                $available[] = $item;
            }
            // otherwise not included (not available at this datetime)
        }

        return $available;
    }

    // Toggle active flag
    public function setActive(int $menu_item_id, bool $isActive): bool
    {
        $stmt = $this->db->prepare("UPDATE menu_items SET is_active = :active, updated_at = NOW() WHERE menu_item_id = :id");
        return $stmt->execute([':active' => $isActive ? 1 : 0, ':id' => $menu_item_id]);
    }


}
