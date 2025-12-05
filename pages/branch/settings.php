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
    echo "<script>alert('Branch not found.'); window.location='./';</script>";
    exit;
}

// -------------------- UPDATE BRANCH --------------------
if (isset($_POST['update_branch'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    if ($name == "" || $email == "") {
        echo "<script>alert('Name and Email are required.');</script>";
    } else {
        // Check duplicate email
        $check = $pdo->prepare("SELECT branch_id FROM branches WHERE email = ? AND branch_id != ?");
        $check->execute([$email, $branch_id]);

        if ($check->rowCount() > 0) {
            echo "<script>alert('Email is already used by another branch.');</script>";
        } else {
            $update = $pdo->prepare("
                UPDATE branches SET
                name = ?, email = ?, phone = ?, address = ?, updated_at = NOW()
                WHERE branch_id = ?
            ");

            if ($update->execute([$name, $email, $phone, $address, $branch_id])) {
                echo "<script>alert('Branch updated successfully!'); window.location='settings.php';</script>";
            } else {
                echo "<script>alert('Update failed.');</script>";
            }
        }
    }
}

// -------------------- DELETE BRANCH --------------------
if (isset($_GET['delete']) && $_GET['delete'] === "yes") {

    // Soft delete
    $del = $pdo->prepare("UPDATE branches SET deleted_at = NOW() WHERE branch_id = ?");
    $del->execute([$branch_id]);

    session_unset();
    session_destroy();
    echo "<script>alert('Branch account deleted.'); window.top.location.href='../home/login.php';</script>";
    exit;
}

// -------------------- LOGOUT --------------------
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    echo "<script>parent.clearSavedPage();parent.navigate(null, '../pages/home/home.php');</script>";
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
                window.location = "panel.php?delete=yes";
            }
        }
    </script>
  </head>
  <body class="branch">
    <main class="page">
      <header class="header header-page">
        <div class="context">
          <h1>
            <a
              >Settings</a
            >
          </h1>
        </div>
      </header>

      <main class="main-container main-scrollable">
        <main class="main">
          <div class="box">
    <h2>Update Branch Info</h2>

    <form method="POST">
        <input type="text" name="name" value="<?= htmlspecialchars($branch['name']) ?>" required>
        <input type="email" name="email" value="<?= htmlspecialchars($branch['email']) ?>" required>
        <input type="text" name="phone" value="<?= htmlspecialchars($branch['phone']) ?>">
        <input type="text" name="address" value="<?= htmlspecialchars($branch['address']) ?>">

        <button class="btn btn-secondary" type="reset" name="update_branch">Cancel</button>
        <button class="btn btn-primary" type="submit" name="update_branch">Update</button>
    </form>

    <br>
    <button class="btn btn-primary" onclick="confirmDelete()"">Delete Account</button>
    <br><br>
    <a href="settings.php?logout=true"><button>Logout</button></a>
</div>
        </main>
      </main>
    </main>

    <aside class="layer" id="add-item">
      <header class="header header-page">
        <div class="actions left">
          <button class="btn btn-secondary layer-close" title="Close Panel">
            <i class="bx bxs-dock-right-arrow btn-icon"></i>
          </button>
        </div>
        <div class="context">
          <h1>Add Item</h1>
        </div>
        <div class="actions right">
          <button
            onclick="parent.navigate(event, '../pages/branch/activity.php')"
            class="btn btn-primary"
            title="Di ko na alam"
          >
            <i class="bx bxs-user-plus btn-icon"></i>
          </button>
        </div>
      </header>

      <main class="main-container main-scrollable">
        <main class="main">Dito Main Content</main>
      </main>
    </aside>

    <aside class="layer" id="edit-item">
      <header class="header header-page">
        <div class="actions left">
          <button class="btn btn-secondary layer-close" title="Close Panel">
            <i class="bx bxs-dock-right-arrow btn-icon"></i>
          </button>
        </div>
        <div class="context">
          <h1>Edit Item</h1>
        </div>
        <div class="actions right">
          <button
            onclick="parent.navigate(event, '../pages/branch/activity.php')"
            class="btn btn-primary"
            title="Di ko na alam"
          >
            <i class="bx bxs-user-plus btn-icon"></i>
          </button>
        </div>
      </header>

      <main class="main-container main-scrollable">
        <main class="main">Dito Sidebar</main>
      </main>
    </aside>
  </body>
</html>
