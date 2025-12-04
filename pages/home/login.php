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

// PROCESS CUSTOMER LOGIN
if (isset($_POST['customer_login'])) {
    $email = trim($_POST['customer_email']);
    $password = trim($_POST['customer_password']);

    if ($email === "" || $password === "") {
        echo "<script>alert('Customer: Email and password required.');</script>";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = ? AND deleted_at IS NULL");
        $stmt->execute([$email]);
        $customer = $stmt->fetch();

        if ($customer && password_verify($password, $customer['password'])) {
            $_SESSION['customer_id'] = $customer['customer_id'];
            header("Location: ../customer/index.php");
            exit;
        } else {
            echo "<script>alert('Customer: Invalid email or password.');</script>";
        }
    }
}

// PROCESS BRANCH LOGIN
if (isset($_POST['branch_login'])) {
    $email = trim($_POST['branch_email']);
    $password = trim($_POST['branch_password']);

    if ($email === "" || $password === "") {
        echo "<script>alert('Branch: Email and password required.');</script>";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM branches WHERE email = ? AND deleted_at IS NULL");
        $stmt->execute([$email]);
        $branch = $stmt->fetch();

        if ($branch && password_verify($password, $branch['password'])) {
            $_SESSION['branch_id'] = $branch['branch_id'];
            header("Location: ../branch/index.php");
            exit;
        } else {
            echo "<script>alert('Branch: Invalid email or password.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />

    <title>Welcome Back!</title>

    <link rel="stylesheet" href="../../assets/css/pages/home/login.css" />

    <script src="../../assets/js/components/slider.js" defer></script>
    <script src="../../assets/js/components/password.js" defer></script>
  </head>

  <body class="home">
    <header class="header">
      <div class="context">
        <h1 class="logo">
          <a onclick="parent.navigate(event, '../pages/home/home.php')"
            >Jollikod.</a
          >
        </h1>
      </div>
      <div class="actions right">
        <button
          onclick="parent.navigate(event, '../pages/home/home.php')"
          class="btn btn-secondary"
        >
          <span class="btn-label">Back to Home</span>
          <i class="bx bxs-home-alt-2 btn-icon"></i>
        </button>
        <button
          onclick="parent.navigate(event, '../pages/home/register.php')"
          class="btn btn-primary"
        >
          <span class="btn-label">Register</span>
          <i class="bx bxs-user-plus btn-icon"></i>
        </button>
      </div>
    </header>

    <main class="main-container">
      <main class="main">
        <div class="forms-container slider">
          <!-- Customer Login -->
          <section class="form-container" id="login-customer">
            <form class="form" method="post" autocomplete="off">
              <h2 class="title">Welcome Back!</h2>
              <div class="input-container">
                <input
                  type="email"
                  name="customer_email"
                  id="customer_email"
                  placeholder="Email"
                  required
                />
                <i class="bx bxs-user"></i>
              </div>
              <div class="input-container">
                <input
                  type="password"
                  name="customer_password"
                  id="customer_password"
                  placeholder="Password"
                  required
                />
                <i class="bx bxs-eye-closed hide-password"></i>
                <i class="bx bxs-eye show-password"></i>
              </div>
              <button type="submit" name="customer_login" class="btn btn-primary">
                Continue as Customer
              </button>
            </form>
          </section>

          <!-- Branch Login -->
          <section class="form-container" id="login-branch">
            <form class="form" method="post" autocomplete="off">
              <h2 class="title">Welcome Back!</h2>
              <div class="input-container">
                <input
                  type="email"
                  name="branch_email"
                  id="branch_email"
                  placeholder="Email"
                  required
                />
                <i class="bx bxs-user"></i>
              </div>
              <div class="input-container">
                <input
                  type="password"
                  name="branch_password"
                  id="branch_password"
                  placeholder="Password"
                  required
                />
                <i class="bx bxs-eye-closed hide-password"></i>
                <i class="bx bxs-eye show-password"></i>
              </div>
              <button type="submit" name="branch_login" class="btn btn-primary">
                Continue as Branch
              </button>
            </form>
          </section>

          <section class="context-container">
            <div class="toggle-panel toggle-left">
              <img
                src="../../assets/res/logos/Jollikod.svg"
                alt="Jollikod"
                class="logo"
              />
              <h4>Managing the restaurant?</h4>
              <button class="btn btn-secondary slider-left-btn">
                Continue as Branch
              </button>
            </div>
            <div class="toggle-panel toggle-right">
              <img
                src="../../assets/res/logos/Jollikod.svg"
                alt="Jollikod"
                class="logo"
              />
              <h4>Just grabbing a bite?</h4>
              <button class="btn btn-secondary slider-right-btn">
                Continue as Customer
              </button>
            </div>
          </section>
        </div>
      </main>
    </main>
  </body>
</html>
