<?php 
abstract class BaseModel {
    protected PDO $db;
    protected $table;

    public function __construct() {
        $this->db = DB::connect();
    }

    // Check if needed
    protected function query($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
