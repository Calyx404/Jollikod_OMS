<?php
class CustomerController {

    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function register() {
        CustomerAuth::check();

        $name = $_POST['name'] ?? null;
        $email = $_POST['email'] ?? null;
        $phone = $_POST['phone'] ?? null;
        $password = $_POST['password'] ?? null;

        if (!$name || !$email || !$password) {
            echo json_encode(['status'=>'error','msg'=>'Missing required fields']);
            return;
        }

        $stmt = $this->pdo->prepare("SELECT * FROM customers WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            echo json_encode(['status'=>'error','msg'=>'Email already exists']);
            return;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $this->pdo->prepare("
            INSERT INTO customers (customer_name, email, phone, password)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([$name, $email, $phone, $hash]);

        echo json_encode(['status'=>'ok','msg'=>'Customer registered successfully']);
    }
}
