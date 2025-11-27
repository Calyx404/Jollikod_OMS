<?php
require_once __DIR__ . '/../core/db.php';

class InventoryStock {
    private $db;

    public function __construct() {
        $this->db = (new DB())->pdo();
    }

    public function getByItem($item_id) {
        $stmt = $this->db->prepare("SELECT * FROM inventory_stocks WHERE inventory_item_id=?");
        $stmt->execute([$item_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($item_id, $quantity=0, $status='in_stock') {
        $stmt = $this->db->prepare("INSERT INTO inventory_stocks(inventory_item_id, stock_quantity, stock_status) VALUES(?,?,?)");
        return $stmt->execute([$item_id, $quantity, $status]);
    }

    public function update($item_id, $quantity, $status) {
        $stmt = $this->db->prepare("UPDATE inventory_stocks SET stock_quantity=?, stock_status=? WHERE inventory_item_id=?");
        return $stmt->execute([$quantity, $status, $item_id]);
    }
}
