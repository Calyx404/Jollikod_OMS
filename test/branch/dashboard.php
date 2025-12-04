<?php
require 'db.php';

// Fetch all customers
$stmt = $pdo->query("SELECT customer_id, name, email, phone, created_at FROM customers");
$customers = $stmt->fetchAll();
?>

<h1>Customer List</h1>
<table border="1">
    <tr>
        <th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Created At</th><th>Actions</th>
    </tr>
    <?php foreach ($customers as $c): ?>
    <tr>
        <td><?= $c['customer_id'] ?></td>
        <td><?= htmlspecialchars($c['name']) ?></td>
        <td><?= htmlspecialchars($c['email']) ?></td>
        <td><?= htmlspecialchars($c['phone']) ?></td>
        <td><?= $c['created_at'] ?></td>
        <td>
            <a href="edit_customer.php?customer_id=<?= $c['customer_id'] ?>">Edit</a> |
            <a href="delete_customer.php?customer_id=<?= $c['customer_id'] ?>">Delete</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
