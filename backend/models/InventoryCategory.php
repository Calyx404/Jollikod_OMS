<?php
require_once __DIR__ . '/../core/db.php';

class InventoryCategory {
    private $db;

    public function __construct() {
        $this->db = $pdo;
    }

    public function all() {
        $stmt = $this->db->query("SELECT * FROM inventory_categories ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($name) {
        $stmt = $this->db->prepare("INSERT INTO inventory_categories(name) VALUES(?)");
        return $stmt->execute([$name]);
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM inventory_categories WHERE inventory_category_id=?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
