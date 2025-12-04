<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = $_POST['phone'];

    $stmt = $pdo->prepare("INSERT INTO customers (name, email, password, phone) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$name, $email, $password, $phone])) {
        echo "Registration successful!";
    } else {
        echo "Error occurred!";
    }
}
?>

<form method="post">
    <input type="text" name="name" placeholder="Full Name" required><br>
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <input type="text" name="phone" placeholder="Phone"><br>
    <button type="submit">Register</button>
</form>
