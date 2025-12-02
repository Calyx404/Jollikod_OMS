<?php

class Payment extends BaseModel
{
    protected string $table = 'payments';
    protected $primaryKey = "payment_id";

    public function create(int $order_id, float $subtotal, float $vat, float $delivery_fee, float $total): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} (order_id, subtotal, vat, delivery_fee, total, status)
            VALUES (:order_id, :subtotal, :vat, :delivery_fee, :total, 'pending')
        ");

        $stmt->execute([
            ':order_id' => $order_id,
            ':subtotal' => $subtotal,
            ':vat' => $vat,
            ':delivery_fee' => $delivery_fee,
            ':total' => $total
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function markPaid(int $payment_id)
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET status = 'paid', updated_at = NOW() WHERE payment_id = :payment_id");
        $stmt->execute([':payment_id' => $payment_id]);
    }

    public function getByOrder(int $order_id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM payments
            WHERE order_id = :order_id
            LIMIT 1
        ");
        $stmt->execute([':order_id' => $order_id]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function getTotalRevenue(int $branch_id, string $from, string $to): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                COALESCE(SUM(total), 0) AS total_revenue,
                COUNT(order_id) AS total_orders
            FROM payments p
            JOIN orders o ON o.order_id = p.order_id
            WHERE o.branch_id = :branch_id 
            AND DATE(o.created_at) BETWEEN :from AND :to
        ");
        $stmt->execute([
            ':branch_id' => $branch_id,
            ':from' => $from,
            ':to' => $to
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


}
