<?php
session_start();
require '../db.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: ../index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM customers WHERE customer_id = ?");
$stmt->execute([$_SESSION['customer_id']]);
$customer = $stmt->fetch();
?>
