<?php

class MenuInventory extends BaseModel
{
    protected string $table = 'menu_inventories';

    public function create(int $menu_id, int $menu_item_id, int $stock_quantity = 0, string $stock_status = 'in_stock'): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} (menu_id, menu_item_id, stock_quantity, stock_status)
            VALUES (:menu_id, :menu_item_id, :stock_quantity, :stock_status)
        ");

        $stmt->execute([
            ':menu_id' => $menu_id,
            ':menu_item_id' => $menu_item_id,
            ':stock_quantity' => $stock_quantity,
            ':stock_status' => $stock_status
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function getStock(int $menu_item_id): int
    {
        $stmt = $this->db->prepare("SELECT stock_quantity FROM {$this->table} WHERE menu_item_id = :menu_item_id");
        $stmt->execute([':menu_item_id' => $menu_item_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['stock_quantity'] : 0;
    }


    public function updateStock(int $menu_item_id, int $quantity)
    {
        $status = $quantity <= 0 ? 'out_of_stock' : ($quantity < 5 ? 'low_stock' : 'in_stock');

        $stmt = $this->db->prepare("
            UPDATE {$this->table} SET stock_quantity = :quantity, stock_status = :status, updated_at = NOW()
            WHERE menu_item_id = :menu_item_id
        ");

        $stmt->execute([
            ':quantity' => $quantity,
            ':status' => $status,
            ':menu_item_id' => $menu_item_id
        ]);
    }

    // Adjust stock by delta (positive or negative). Returns new quantity.
    public function adjustStock(int $menu_item_id, int $delta, ?int $staff_id = null, ?int $menu_id = null): int
    {
        // Start transaction to avoid race conditions
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("SELECT stock_quantity FROM {$this->table} WHERE menu_item_id = :menu_item_id FOR UPDATE");
            $stmt->execute([':menu_item_id' => $menu_item_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $current = $row ? (int)$row['stock_quantity'] : 0;
            $newQty = max(0, $current + $delta);

            $status = $newQty <= 0 ? 'out_of_stock' : ($newQty < 5 ? 'low_stock' : 'in_stock');

            $update = $this->db->prepare("
                UPDATE {$this->table} SET stock_quantity = :qty, stock_status = :status, updated_at = NOW()
                WHERE menu_item_id = :menu_item_id
            ");
            $update->execute([':qty' => $newQty, ':status' => $status, ':menu_item_id' => $menu_item_id]);

            // Optionally create a menu log (if staff_id provided)
            if ($staff_id !== null) {
                // find menu_id if not provided
                if ($menu_id === null) {
                    $q = $this->db->prepare("SELECT menu_id FROM menu_items WHERE menu_item_id = :id LIMIT 1");
                    $q->execute([':id' => $menu_item_id]);
                    $mrow = $q->fetch(PDO::FETCH_ASSOC);
                    $menu_id = $mrow ? (int)$mrow['menu_id'] : null;
                }

                $desc = "Stock adjusted by {$delta}. New qty: {$newQty}";
                $logStmt = $this->db->prepare("
                    INSERT INTO menu_logs (menu_item_id, staff_id, description) VALUES (:menu_item_id, :staff_id, :description)
                ");
                $logStmt->execute([':menu_item_id' => $menu_item_id, ':staff_id' => $staff_id, ':description' => $desc]);
            }

            $this->db->commit();
            return $newQty;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }


    public function getByMenu(int $menu_id): array
    {
        $stmt = $this->db->prepare("
            SELECT mi.*, inv.stock_quantity, inv.stock_status 
            FROM menu_items mi
            JOIN {$this->table} inv ON mi.menu_item_id = inv.menu_item_id
            WHERE mi.menu_id = :menu_id
        ");
        $stmt->execute([':menu_id' => $menu_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getInventorySummaryByBranch(int $branch_id): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                mi.menu_item_id,
                mi.name,
                mi.price,
                mi.description,
                COALESCE(mi.image_path, '') AS image_path,
                inv.stock_quantity,
                inv.stock_status
            FROM menu_items mi
            JOIN menus m ON m.menu_id = mi.menu_id
            JOIN menu_inventories inv ON inv.menu_item_id = mi.menu_item_id
            WHERE m.branch_id = :branch_id
            ORDER BY mi.name ASC
        ");
        $stmt->execute([':branch_id' => $branch_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
