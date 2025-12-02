<?php

class OrderLog extends BaseModel
{
    protected string $table = 'order_logs';

    public function create(int $order_id, string $status)
    {
        $timestampField = match($status) {
            'queued' => 'queued_at',
            'preparing' => 'preparing_at',
            'delivering' => 'delivering_at',
            'delivered' => 'delivered_at',
            'received' => 'received_at',
            default => null
        };

        if ($timestampField) {
            $stmt = $this->db->prepare("INSERT INTO {$this->table} (order_id, {$timestampField}) VALUES (:order_id, NOW())");
            $stmt->execute([':order_id' => $order_id]);
        }
    }

    public function listByOrder(int $order_id): array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM order_logs
            WHERE order_id = :order_id
            ORDER BY created_at ASC
        ");
        $stmt->execute([':order_id' => $order_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

}
