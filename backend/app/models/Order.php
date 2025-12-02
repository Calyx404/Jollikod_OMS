<?php

class Order extends BaseModel
{
    protected string $table = 'orders';

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} 
            (branch_id, customer_id, item_quantity, store_address, destination_address, status)
            VALUES 
            (:branch_id, :customer_id, :item_quantity, :store_address, :destination_address, :status)
        ");

        $stmt->execute([
            ':branch_id' => $data['branch_id'],
            ':customer_id' => $data['customer_id'],
            ':item_quantity' => $data['item_quantity'] ?? 0,
            ':store_address' => $data['store_address'] ?? '',
            ':destination_address' => $data['destination_address'] ?? '',
            ':status' => $data['status'] ?? 'placed'
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function find(int $order_id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE order_id = :order_id");
        $stmt->execute([':order_id' => $order_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function updateStatus(int $order_id, string $status)
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET status = :status, updated_at = NOW() WHERE order_id = :order_id");
        $stmt->execute([':status' => $status, ':order_id' => $order_id]);
    }

    public function listByCustomer(int $customer_id): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE customer_id = :customer_id ORDER BY created_at DESC");
        $stmt->execute([':customer_id' => $customer_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listByBranch(int $branch_id, string $status = ''): array
    {
        if ($status) {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE branch_id = :branch_id AND status = :status ORDER BY created_at DESC");
            $stmt->execute([':branch_id' => $branch_id, ':status' => $status]);
        } else {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE branch_id = :branch_id ORDER BY created_at DESC");
            $stmt->execute([':branch_id' => $branch_id]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrdersByStatus(int $branch_id, string $status, string $from, string $to): array
    {
        $stmt = $this->db->prepare("
            SELECT o.order_id, c.name AS customer_name, o.item_quantity, o.status, o.created_at, o.updated_at
            FROM orders o
            JOIN customers c ON c.customer_id = o.customer_id
            WHERE o.branch_id = :branch_id
            AND o.status = :status
            AND DATE(o.created_at) BETWEEN :from AND :to
            ORDER BY o.created_at ASC
        ");

        $stmt->execute([
            ':branch_id' => $branch_id,
            ':status' => $status,
            ':from' => $from,
            ':to' => $to
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
