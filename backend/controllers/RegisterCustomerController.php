<?php
function connectDB() {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $dbname = 'jollikod_oms';

    $conn = new mysqli($host, $user, $pass, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// ====== CREATE ======
function createCustomer($name, $email, $password) {
    $conn = connectDB();
    $stmt = $conn->prepare("INSERT INTO customers (name, email, hash_password) VALUES (?)");
    $stmt->bind_param("sss", $name, $email, $password);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

// ====== READ ======
function readCustomers() {
    $conn = connectDB();
    $result = $conn->query("SELECT * FROM customers ORDER BY `customer_id`");
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    $conn->close();
    return $users;
}

// ====== ACTION HANDLER ======
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create' && !empty($_POST['name']) && !empty($_POST['email']) && !empty($_POST['password'])) {
        createCustomer($_POST['name'], $_POST['email'], $_POST['password']);
        header("Location: index.php");
        exit;
    }
}
