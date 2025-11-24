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
function createUser($name) {
    $conn = connectDB();
    $stmt = $conn->prepare("INSERT INTO users (name) VALUES (?)");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

// ====== READ ======
function readUsers() {
    $conn = connectDB();
    $result = $conn->query("SELECT * FROM users ORDER BY id ASC");
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

    if ($action === 'create' && !empty($_POST['name'])) {
        createUser($_POST['name']);
        header("Location: index.php");
        exit;
    }
}
