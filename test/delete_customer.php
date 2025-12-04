<?php
require 'db.php';
$id = $_GET['customer_id'];
$stmt = $pdo->prepare("DELETE FROM customers WHERE customer_id=?");
$stmt->execute([$id]);
header('Location: dashboard.php');
?>
