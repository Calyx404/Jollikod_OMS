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

// -------------------- DELETE --------------------
if (isset($_GET['delete']) && $_GET['delete'] === "yes") {
    // Soft delete
    $del = $pdo->prepare("UPDATE customers SET deleted_at = NOW() WHERE customer_id = ?");
    $del->execute([$customer_id]);

    session_unset();
    session_destroy();
    echo "<script>alert('Customer account deleted.'); window.top.location.reload();</script>";
    exit;
}

// -------------------- LOGOUT --------------------
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    echo "<script>alert('Logged out.'); window.top.location.reload();</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Account Settings</title>

    <link rel="stylesheet" href="../../assets/css/pages/customer/settings.css" />

    <script src="../../assets/js/components/layer.js" defer></script>

    <script>
        function confirmDelete() {
            if (confirm("Are you sure you want to delete this customer account? This cannot be undone.")) {
              window.location = 'settings.php?delete=yes';
            }
        }

        function logout() {
          window.location = 'settings.php?logout=true';
        }
    </script>
  </head>
  <body class="customer">
    <main class="page">
      <header class="header header-page">
        <div class="context">
          <h1>Settings</h1>
        </div>

        <div class="actions right">
          <button
            onclick="parent.navigate('./account.php')"
            class="btn btn-secondary subnav"
          >
            <span class="btn-label">Profile</span>
            <i class="bx bxs-user btn-icon"></i>
          </button>

          <button
            class="btn btn-primary"
          >
            <span class="btn-label">Apply Changes</span>
            <i class="bx bxs-save btn-icon"></i>
          </button>
        </div>
      </header>

      <main class="main-container main-scrollable">
        <main class="main">
          <section class="settings-container">
            <div class="settings-item">
                <h4>Sounds</h4>

                <div class="settings-field">
                    <span>Background</span>
                    <label class="switch">
                        <input type="checkbox" checked >
                        <span class="slider round"></span>
                    </label>
                </div>
                
                <div class="settings-field">
                    <span>Notificatons</span>
                    <label class="switch">
                        <input type="checkbox" checked >
                        <span class="slider round"></span>
                    </label>
                </div>
                
                <div class="settings-field">
                    <span>Haptics</span>
                    <label class="switch">
                        <input type="checkbox" >
                        <span class="slider round"></span>
                    </label>
                </div>

            </div>

            <div class="settings-item">
                <h4>Legal</h4>

                <div class="settings-field">
                    <p>Terms of Use</p>
                    <i class='bx  bxs-article'></i> 
                </div>

                <div class="settings-field">
                    <p>Privacy Policy</p>
                    <i class='bx  bxs-shield-alt-2'></i> 
                </div>
            </div>

            <div class="settings-item">
                <h4>About</h4>

                <div class="settings-field">
                    <p>App Version</p>
                    <strong>v1.0.0</strong>
                </div>

                <div class="settings-field">
                    <p>Rate This App</p>
                    <i class='bx  bxs-star'></i> 
                </div>

                <div class="settings-field">
                    <p>About This App</p>
                    <i class='bx  bxs-info-circle'></i> 
                </div>
            </div>

            <div class="settings-actions">
                <button class="btn btn-secondary" onclick="confirmDelete()">Delete Account</button>
                <button class="btn btn-primary" onclick="logout()">Logout</button>
            </div>
            
          </section>
        </main>
      </main>
    </main>

  </body>
</html>
