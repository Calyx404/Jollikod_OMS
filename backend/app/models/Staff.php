<?php

class Staff extends BaseModel
{
    protected string $table = 'staff';

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} (name, branch_id, email, phone, password, role)
            VALUES (:name, :branch_id, :email, :phone, :password, :role)
        ");

        $stmt->execute([
            ':name' => $data['name'],
            ':branch_id' => $data['branch_id'],
            ':email' => $data['email'],
            ':phone' => $data['phone'] ?? null,
            ':password' => $data['password'],
            ':role' => $data['role'] ?? 'staff'
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function update(int $staff_id, array $data): bool
    {
        $fields = [];
        $params = [':staff_id' => $staff_id];

        if (isset($data['name'])) {
            $fields[] = "name = :name";
            $params[':name'] = $data['name'];
        }
        if (isset($data['email'])) {
            $fields[] = "email = :email";
            $params[':email'] = $data['email'];
        }
        if (isset($data['phone'])) {
            $fields[] = "phone = :phone";
            $params[':phone'] = $data['phone'];
        }
        if (isset($data['password'])) {
            $fields[] = "password = :password";
            $params[':password'] = $data['password'];
        }
        if (isset($data['role'])) {
            $fields[] = "role = :role";
            $params[':role'] = $data['role'];
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE staff_id = :staff_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function deactivate(int $staff_id): bool
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET status = 'inactive', updated_at = NOW() WHERE staff_id = :staff_id");
        return $stmt->execute([':staff_id' => $staff_id]);
    }

    public function activate(int $staff_id): bool
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET status = 'active', updated_at = NOW() WHERE staff_id = :staff_id");
        return $stmt->execute([':staff_id' => $staff_id]);
    }

    public function findById(int $staff_id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE staff_id = :staff_id LIMIT 1");
        $stmt->execute([':staff_id' => $staff_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function listByBranch(int $branch_id): array
    {
        $stmt = $this->db->prepare("SELECT staff_id, name, email, phone, role, status, created_at FROM {$this->table} WHERE branch_id = :branch_id ORDER BY created_at DESC");
        $stmt->execute([':branch_id' => $branch_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function changeRole(int $staff_id, string $role): bool
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET role = :role, updated_at = NOW() WHERE staff_id = :staff_id");
        return $stmt->execute([':role' => $role, ':staff_id' => $staff_id]);
    }

    public function findByEmailAndBranch(string $email, int $branch_id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = :email AND branch_id = :branch_id LIMIT 1");
        $stmt->execute([':email' => $email, ':branch_id' => $branch_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
