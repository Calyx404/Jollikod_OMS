<?php
session_start();
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $pass = trim($_POST['password']);

    if (empty($email) || empty($pass)) {
        echo "<script>alert('Email and password required.'); history.back();</script>";
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = ? AND deleted_at IS NULL");
    $stmt->execute([$email]);
    $customer = $stmt->fetch();

    if (!$customer || !password_verify($pass, $customer['password'])) {
        echo "<script>alert('Invalid credentials.'); history.back();</script>";
        exit;
    }

    $_SESSION['customer_id'] = $customer['customer_id'];
    header("Location: ../customer/profile.php");
    exit;
}
?>
