<?php
class CustomerController {

    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function register() {
        BranchAuthMiddleware::handle();
    
        $owner = $_POST['owner_name'] ?? null;
        $email = $_POST['email'] ?? null;
        $password = $_POST['password'] ?? null;
        $city = $_POST['city'] ?? null;
        $province = $_POST['province'] ?? null;
    
        if (!$owner || !$email || !$password) {
            echo json_encode(['status'=>'error','msg'=>'Missing fields']);
            return;
        }
    
        $stmt = $this->pdo->prepare("SELECT * FROM branches WHERE email = ?");
        $stmt->execute([$email]);
    
        if ($stmt->fetch()) {
            echo json_encode(['status'=>'error','msg'=>'Email already exists']);
            return;
        }
    
        $hash = password_hash($password, PASSWORD_BCRYPT);
    
        $stmt = $this->pdo->prepare("
            INSERT INTO branches (owner_name, email, password, city, province)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$owner, $email, $hash, $city, $province]);
    
        echo json_encode(['status'=>'ok','msg'=>'Branch registered successfully']);
    }
    
}
