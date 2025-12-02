<?php

class OrderItem extends BaseModel
{
    protected string $table = 'order_items';

    public function create(int $order_id, int $menu_item_id, int $quantity, float $unit_price, float $total): int
    {
        if ($total == 0) {
            $total = $quantity * $unit_price;
        }

        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} (order_id, menu_item_id, quantity, unit_price, total)
            VALUES (:order_id, :menu_item_id, :quantity, :unit_price, :total)
        ");

        $stmt->execute([
            ':order_id' => $order_id,
            ':menu_item_id' => $menu_item_id,
            ':quantity' => $quantity,
            ':unit_price' => $unit_price,
            ':total' => $total
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function listByOrder(int $order_id): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE order_id = :order_id");
        $stmt->execute([':order_id' => $order_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopItems(int $branch_id, string $from, string $to): array
    {
        $stmt = $this->db->prepare("
            SELECT mi.name AS item_name, SUM(oi.quantity) AS units_sold, SUM(oi.total) AS revenue
            FROM order_items oi
            JOIN orders o ON o.order_id = oi.order_id
            JOIN menu_items mi ON mi.menu_item_id = oi.menu_item_id
            WHERE o.branch_id = :branch_id 
            AND DATE(o.created_at) BETWEEN :from AND :to
            GROUP BY oi.menu_item_id
            ORDER BY units_sold DESC
            LIMIT 10
        ");
        $stmt->execute([
            ':branch_id' => $branch_id,
            ':from' => $from,
            ':to' => $to
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSalesLog(int $branch_id, string $from, string $to): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                o.order_id,
                o.created_at AS order_date,
                c.name AS customer_name,
                SUM(oi.quantity) AS total_items,
                SUM(oi.total) AS total_amount
            FROM orders o
            JOIN order_items oi ON oi.order_id = o.order_id
            JOIN customers c ON c.customer_id = o.customer_id
            WHERE o.branch_id = :branch_id
            AND DATE(o.created_at) BETWEEN :from AND :to
            GROUP BY o.order_id
            ORDER BY o.created_at ASC
        ");
        $stmt->execute([
            ':branch_id' => $branch_id,
            ':from' => $from,
            ':to' => $to
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
