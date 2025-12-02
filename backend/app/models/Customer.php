<?php

class Customer extends BaseModel
{
    protected string $table = 'customers';

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} (name, email, phone, password, address)
            VALUES (:name, :email, :phone, :password, :address)
        ");

        $stmt->execute([
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':phone' => $data['phone'] ?? null,
            ':password' => $data['password'],
            ':address' => $data['address'] ?? null
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    public function getRetentionStats(int $branch_id, string $from, string $to): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(DISTINCT c.customer_id) AS total_customers,
                SUM(CASE WHEN orders_count > 1 THEN 1 ELSE 0 END) AS returning_customers
            FROM (
                SELECT c.customer_id, COUNT(o.order_id) AS orders_count
                FROM customers c
                JOIN orders o ON o.customer_id = c.customer_id
                WHERE o.branch_id = :branch_id 
                AND DATE(o.created_at) BETWEEN :from AND :to
                GROUP BY c.customer_id
            ) t
        ");
        $stmt->execute([
            ':branch_id' => $branch_id,
            ':from' => $from,
            ':to' => $to
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getCustomerDemographics(int $branch_id, string $from, string $to): array
{
    $stmt = $this->db->prepare("
        SELECT 
            c.customer_id,
            c.name,
            COUNT(o.order_id) AS total_orders,
            COALESCE(SUM(p.total),0) AS total_spent,
            MAX(o.created_at) AS last_order_date,
            CASE WHEN COUNT(o.order_id) > 0 THEN COALESCE(SUM(p.total)/COUNT(o.order_id),0) ELSE 0 END AS avg_order_value
        FROM customers c
        LEFT JOIN orders o ON o.customer_id = c.customer_id
        LEFT JOIN payments p ON p.order_id = o.order_id
        WHERE o.branch_id = :branch_id 
          AND DATE(o.created_at) BETWEEN :from AND :to
        GROUP BY c.customer_id
        ORDER BY total_spent DESC
    ");
    $stmt->execute([
        ':branch_id' => $branch_id,
        ':from' => $from,
        ':to' => $to
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

}
