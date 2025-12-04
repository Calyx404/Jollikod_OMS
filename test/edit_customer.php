<?php
require 'db.php';
$id = $_GET['customer_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $stmt = $pdo->prepare("UPDATE customers SET name=?, email=?, phone=? WHERE customer_id=?");
    $stmt->execute([$name, $email, $phone, $id]);
    header('Location: dashboard.php');
}

$stmt = $pdo->prepare("SELECT * FROM customers WHERE customer_id=?");
$stmt->execute([$id]);
$customer = $stmt->fetch();
?>

<form method="post">
    <input type="text" name="name" value="<?= htmlspecialchars($customer['name']) ?>" required><br>
    <input type="email" name="email" value="<?= htmlspecialchars($customer['email']) ?>" required><br>
    <input type="text" name="phone" value="<?= htmlspecialchars($customer['phone']) ?>"><br>
    <button type="submit">Update</button>
</form>
