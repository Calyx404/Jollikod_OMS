<?php
require_once __DIR__ . '/../core/db.php';

class InventoryItem {
    private $db;

    public function __construct() {
        $this->db = (new DB())->pdo();
    }

    public function all($branch_id) {
        $sql = "SELECT i.*, c.name as category_name, s.stock_quantity, s.stock_status
                FROM inventory_items i
                JOIN inventory_categories c ON i.inventory_category_id = c.inventory_category_id
                LEFT JOIN inventory_stocks s ON i.inventory_item_id = s.inventory_item_id
                WHERE i.inventory_id = ?
                ORDER BY c.name, i.name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$branch_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO inventory_items(inventory_id, inventory_category_id, name, description, price, image_path)
                                    VALUES (?,?,?,?,?,?)");
        return $stmt->execute([
            $data['inventory_id'],
            $data['inventory_category_id'],
            $data['name'],
            $data['description'] ?? '',
            $data['price'] ?? 0,
            $data['image_path'] ?? null
        ]);
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM inventory_items WHERE inventory_item_id=?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE inventory_items SET inventory_category_id=?, name=?, description=?, price=?, image_path=? WHERE inventory_item_id=?");
        return $stmt->execute([
            $data['inventory_category_id'],
            $data['name'],
            $data['description'] ?? '',
            $data['price'],
            $data['image_path'] ?? null,
            $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM inventory_items WHERE inventory_item_id=?");
        return $stmt->execute([$id]);
    }
}
