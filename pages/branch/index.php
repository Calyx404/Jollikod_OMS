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
    header("Location: ../home/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Branch Dashboard</title>

    <link rel="stylesheet" href="../../assets/css/pages/branch/index.css" />

    <script src="../../assets/js/components/navigation.js" defer></script>
  </head>
  <body class="branch">
    <nav class="navbar">
      <img
        src="../../assets/res/logos/Jollikod.svg"
        alt="Jollikod"
        class="logo"
      />

      <hr class="nav-hr" />

      <ul class="pages-container" id="nav">
        <li
          class="page-container active-page"
          id="menu"
          data-target="./menu.php"
        >
          <i class="bx bxs-dish page-icon"></i>
          <span class="page-label">Menu</span>
        </li>
        <li class="page-container" id="activity" data-target="./activity.php">
          <i class="bx bxs-basket page-icon"></i>
          <span class="page-label">Activity</span>
        </li>

        <ul class="pages-container" id="more">
          <li
            class="page-container hide-page"
            id="more-collapsed"
            data-target="./store.php"
          >
            <!-- Change navigate to a modal -->
            <i class="bx bxs-circles page-icon"></i>
            <span class="page-label">More</span>
          </li>

          <ul class="pages-container" id="more-expanded">
            <li class="page-container" id="store" data-target="./store.php">
              <i class="bx bxs-store page-icon"></i>
              <span class="page-label">Store</span>
            </li>
            <li
              class="page-container"
              id="customer"
              data-target="./customer.php"
            >
              <i class="bx bxs-fork-spoon page-icon"></i>
              <span class="page-label">Customer</span>
            </li>
            <li class="page-container" id="staff" data-target="./staff.php">
              <i class="bx bxs-chef-hat page-icon"></i>
              <span class="page-label">Staff</span>
            </li>
          </ul>
        </ul>

        <ul class="pages-container" id="account">
          <li
            class="page-container hide-page"
            id="account-collapsed"
            data-target="./account.php"
          >
            <i class="bx bxs-user page-icon"></i>
            <span class="page-label">Account</span>
          </li>

          <ul class="pages-container" id="account-expanded">
            <li
              class="page-container"
              id="settings"
              data-target="./settings.php"
            >
              <i class="bx bxs-cog page-icon"></i>
              <span class="page-label">Settings</span>
            </li>

            <hr class="nav-hr" />

            <li
              class="page-container"
              id="profile"
              data-target="./account.php"
            >
              <i class="bx bxs-user page-icon"></i>
              <span class="page-label">Profile</span>
            </li>
          </ul>
        </ul>
      </ul>
    </nav>

    <iframe
      id="page-frame"
      frameborder="0"
      class="frame"
      src="./menu.php"
    ></iframe>
  </body>
</html>
