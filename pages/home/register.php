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

// PROCESS CUSTOMER REGISTRATION 
if (isset($_POST['customer_register'])) {
    $name = trim($_POST['customer_name']);
    $email = trim($_POST['customer_email']);
    $password = trim($_POST['customer_password']);
    $phone = trim($_POST['customer_phone']);

    if ($name === "" || $email === "" || $password === "") {
        echo "<script>alert('Customer: All fields are required.');</script>";
    } else {
        $check = $pdo->prepare("SELECT customer_id FROM customers WHERE email = ?");
        $check->execute([$email]);

        if ($check->rowCount() > 0) {
            echo "<script>alert('Customer: Email already exists.');</script>";
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO customers (name, email, password, phone) VALUES (?, ?, ?, ?)");

            if ($stmt->execute([$name, $email, $hash, $phone])) {
                echo "<script>alert('Customer registered successfully!');</script>";
                $_SESSION['customer_id'] = $customer['customer_id'];
            header("Location: ../customer/index.php");
            exit;
            } else {
                echo "<script>alert('Customer: Registration failed.');</script>";
                
            }
        }
    }
}

// PROCESS BRANCH REGISTRATION 
if (isset($_POST['branch_register'])) {
    $name = trim($_POST['branch_name']);
    $email = trim($_POST['branch_email']);
    $password = trim($_POST['branch_password']);
    $phone = trim($_POST['branch_phone']);
    $address = trim($_POST['branch_address']);

    if ($name === "" || $email === "" || $password === "") {
        echo "<script>alert('Branch: All fields are required.');</script>";
    } else {
        $check = $pdo->prepare("SELECT branch_id FROM branches WHERE email = ?");
        $check->execute([$email]);

        if ($check->rowCount() > 0) {
            echo "<script>alert('Branch: Email already exists.');</script>";
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO branches (name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)");

            if ($stmt->execute([$name, $email, $hash, $phone, $address])) {
                echo "<script>alert('Branch registered successfully!');</script>";
                $_SESSION['branch_id'] = $branch['branch_id'];
            header("Location: ../branch/index.php");
            exit;
            } else {
                echo "<script>alert('Branch: Registration failed.');</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />

    <title>Get Started!</title>

    <link rel="stylesheet" href="../../assets/css/pages/home/register.css" />

    <script src="../../assets/js/components/slider.js" defer></script>
    <script src="../../assets/js/components/password.js" defer></script>

    <script src="../../assets/js/routes/api.js" defer></script>
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
          onclick="parent.navigate(event, '../pages/home/login.php')"
          class="btn btn-primary"
        >
          <span class="btn-label">Log In</span>
          <i class="bx bxs-door-open btn-icon"></i>
        </button>
      </div>
    </header>

    <main class="main-container">
      <main class="main">
        <div class="forms-container slider">
          <!-- Customer Register -->
          <section class="form-container" id="register-customer">
            <form class="form" method="POST" autocomplete="off">
              <h2 class="title">Let's Get Started!</h2>

              <div class="input-container">
                <input
                  type="text"
                  name="customer_name"
                  id="customer_name"
                  placeholder="Full Name"
                  required
                />
                <i class="bx bxs-user"></i>
              </div>

              <div class="input-container">
                <input
                  type="email"
                  name="customer_email"
                  id="customer_email"
                  placeholder="your.email@example.com"
                  required
                />
                <i class="bx bxs-envelope"></i>
              </div>

              <div class="input-container">
                <input
                  type="phone"
                  name="customer_phone"
                  id="customer_phone"
                  placeholder="09XX-XXX-XXXX"
                  maxlength="16"
                  required
                />
                <i class="bx bxs-phone"></i>
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

              <button type="submit" name="customer_register" class="btn btn-primary">
                Register as Customer
              </button>
            </form>
          </section>

          <!-- Branch Register -->
          <section class="form-container" id="register-branch">
            <form class="form" method="POST" autocomplete="off">
              <h2 class="title">Let's Get Started!</h2>

              <div class="input-container">
                <input
                  type="text"
                  name="branch_name"
                  id="branch_name"
                  placeholder="Username"
                  required
                />
                <i class="bx bxs-user"></i>
              </div>

              <div class="input-container">
                <input
                  type="email"
                  name="branch_email"
                  id="branch_email"
                  placeholder="your.email@gmail.com"
                  required
                />
                <i class="bx bxs-envelope"></i>
              </div>

              <div class="input-container">
                <input
                  type="phone"
                  name="branch_phone"
                  id="branch_phone"
                  placeholder="+63-9XX-XXX-XXXX"
                  maxlength="16"
                  required
                />
                <i class="bx bxs-phone"></i>
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

              <button type="submit" name="branch_register" class="btn btn-primary">
                Register as Branch
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
                Register as Branch
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
                Register as Customer
              </button>
            </div>
          </section>
        </div>
      </main>
    </main>
  </body>
</html>
