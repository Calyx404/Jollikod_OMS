<?php
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $pass = trim($_POST['password']);
    $phone = trim($_POST['phone']);

    if (empty($name) || empty($email) || empty($pass)) {
        echo "<script>alert('All fields are required.'); history.back();</script>";
        exit;
    }

    // Check duplicate email
    $stmt = $pdo->prepare("SELECT email FROM customers WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        echo "<script>alert('Email already exists. Choose another.'); history.back();</script>";
        exit;
    }

    // Insert new customer
    $hash = password_hash($pass, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO customers (name, email, password, phone) VALUES (?, ?, ?, ?)");

    if ($stmt->execute([$name, $email, $hash, $phone])) {
        echo "<script>alert('Registration Successful!'); window.location='../index.php';</script>";
    } else {
        echo "<script>alert('Registration failed. Try again.'); history.back();</script>";
    }
}
?>
