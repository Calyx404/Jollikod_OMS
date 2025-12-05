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
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Staff Management</title>

    <link rel="stylesheet" href="../../assets/css/pages/branch/staff.css" />

    <script src="../../assets/js/components/layer.js" defer></script>
  </head>
  <body class="branch">
    <main class="page">
      <header class="header header-page">
        <div class="context">
          <h1>
            <a onclick="parent.navigate(event, '../pages/home/home.php')"
              >Staff</a
            >
          </h1>
        </div>
        <div class="actions right">
          <button
            onclick="parent.navigate('./store.php')"
            class="btn btn-secondary subnav"
          >
            <span class="btn-label">Store</span>
            <i class="bx bxs-store btn-icon"></i>
          </button>

          <button
            onclick="parent.navigate('./customer.php')"
            class="btn btn-primary subnav"
          >
            <span class="btn-label">Customer</span>
            <i class="bx bxs-fork-spoon btn-icon"></i>
          </button>
        </div>
      </header>

      <main class="main-container main-scrollable">
        <main class="main">Arf</main>
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
