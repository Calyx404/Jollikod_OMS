<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Jollikod - Bida ka!</title>
</head>
<body>
  <h2>Customer List</h2>

  <?php

  include '../services/controllers/RegisterCustomerController.php';
  $customers = readCustomers();
  ?>

  <table border="1" cellpadding="8" cellspacing="0">
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($customers as $customer): ?>
        <tr>
          <td><?= htmlspecialchars($customer['id']) ?></td>
          <td><?= htmlspecialchars($customer['name']) ?></td>
          <td><?= htmlspecialchars($customer['email']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <hr>

  <h3>Add New Customer</h3>
  <form action="../services/controllers/RegisterCustomerController.php" method="POST">
    <input type="text" name="name" placeholder="Enter name" required>
    <input type="email" name="email" placeholder="Enter email" required>
    <input type="password" name="password" placeholder="Enter password" required>
    <button type="submit" name="action" value="create">Submit</button>
  </form>
</body>
</html>
