<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();
require '../../database/connection.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: ../customer/");
    exit;
}

$customer_id = $_SESSION['customer_id'];

// Fetch customer data
$stmt = $pdo->prepare("SELECT * FROM customers WHERE customer_id = ? AND deleted_at IS NULL");
$stmt->execute([$customer_id]);
$customer = $stmt->fetch();

if (!$customer) {
    echo "<script>alert('Customer not found.'); parent.navigate(null, '../pages/home/home.php');</script>";
    exit;
}

// -------------------- UPDATE --------------------

if (isset($_POST['update_customer'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    if ($name == "" || $email == "") {
        echo "<script>alert('Name and Email are required.');</script>";
    } else {
        // Check duplicate email
        $check = $pdo->prepare("SELECT customer_id FROM customers WHERE email = ? AND customer_id != ?");
        $check->execute([$email, $customer_id]);

        if ($check->rowCount() > 0) {
            echo "<script>alert('Email is already used by another customer.');</script>";
        } else {
            $update = $pdo->prepare("
                UPDATE customers SET
                name = ?, email = ?, phone = ?, address = ?, updated_at = NOW()
                WHERE customer_id = ?
            ");

            if ($update->execute([$name, $email, $phone, $address, $customer_id])) {
                echo "<script>alert('Customer updated successfully!'); parent.navigate(null, './account.php');</script>";
            } else {
                echo "<script>alert('Update failed.');</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Account Profile</title>

    <link rel="stylesheet" href="../../assets/css/pages/customer/account.css" />

    <script src="../../assets/js/components/layer.js" defer></script>
  </head>
  <body class="customer">
    <main class="page">
      <header class="header header-page">
        <div class="context">
          <h1>Profile</h1>
        </div>
        <div class="actions right">
        <button
          onclick="parent.navigate('./settings.php')"
          class="btn btn-primary subnav"
        >
          <span class="btn-label">Settings</span>
          <i class="bx bxs-cog btn-icon"></i>
        </button>
      </div>
      </header>

      <main class="main-container main-scrollable">
        <main class="main">
          <section class="form-container">

            <form class="form" method="POST" autocomplete="off">
                <div class="account-field">
                    <label for="name">
                    <h4>Name</h4>
                  </label>
                  <div class="input-container">
                    <input type="text" name="name" placeholder="Full Name" value="<?= htmlspecialchars($customer['name']) ?>" required>
                    <i class="bx bxs-user"></i>
                  </div>
                </div>
                
                <div class="account-field">
                  <label for="email">
                    <h4>Email</h4>
                  </label>
                      <div class="input-container">
                    <input type="email" name="email" placeholder="your.email@example.com" value="<?= htmlspecialchars($customer['email']) ?>" required>
                    <i class="bx bxs-envelope"></i>
                  </div>
                </div>

                <div class="account-field">
                  <label for="phone">
                    <h4>Phone</h4>
                  </label>
                      <div class="input-container">
                    <input type="phone" name="phone" placeholder="09XX-XXX-XXXX" value="<?= htmlspecialchars($customer['phone']) ?>" required>
                    <i class="bx bxs-phone"></i>
                  </div>
                </div>

                <div class="account-field">
                  <label for="address">
                    <h4>Address</h4>
                  </label>
                      <div class="input-container">
                    <input type="text" name="address" placeholder="Lot, Street, City, Province" value="<?= htmlspecialchars($customer['address']) ?>">
                    <i class="bx bxs-location"></i>
                  </div>
                </div>

                <div class="account-actions">
                  <button class="btn btn-secondary" type="reset" name="update_customer">Cancel</button>
                  <button class="btn btn-primary" type="submit" name="update_customer">Update</button>
                </div>
            </form>
          </section>

        </main>
      </main>
    </main>

  </body>
</html>
