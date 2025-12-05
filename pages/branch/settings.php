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

if (!isset($_SESSION['branch_id'])) {
    header("Location: ../branch/");
    exit;
}

$branch_id = $_SESSION['branch_id'];

// Fetch branch data
$stmt = $pdo->prepare("SELECT * FROM branches WHERE branch_id = ? AND deleted_at IS NULL");
$stmt->execute([$branch_id]);
$branch = $stmt->fetch();

if (!$branch) {
    echo "<script>alert('Branch not found.'); parent.navigate(null, '../pages/home/home.php');</script>";
    exit;
}

// -------------------- DELETE BRANCH --------------------
if (isset($_GET['delete']) && $_GET['delete'] === "yes") {
    // Soft delete
    $del = $pdo->prepare("UPDATE branches SET deleted_at = NOW() WHERE branch_id = ?");
    $del->execute([$branch_id]);

    session_unset();
    session_destroy();
    echo "<script>alert('Branch account deleted.'); window.top.location.reload();</script>";
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

    <link rel="stylesheet" href="../../assets/css/pages/branch/settings.css" />

    <script src="../../assets/js/components/layer.js" defer></script>

    <script>
        function confirmDelete() {
            if (confirm("Are you sure you want to delete this branch account? This cannot be undone.")) {
              window.location = 'settings.php?delete=yes';
            }
        }

        function logout() {
          window.location = 'settings.php?logout=true';
        }
    </script>
  </head>
  <body class="branch">
    <main class="page">
      <header class="header header-page">
        <div class="context">
          <h1>Settings</h1>
        </div>
      </header>

      <main class="main-container main-scrollable">
        <main class="main">
              <button class="btn btn-secondary" onclick="confirmDelete()">Delete Account</button>
              <button class="btn btn-primary" onclick="logout()">Logout</button>
        </main>
      </main>
    </main>

  </body>
</html>
