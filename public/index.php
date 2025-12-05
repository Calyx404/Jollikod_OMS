<?php
session_start();
$startPage = "../pages/home/home.php";

if (isset($_SESSION['branch_id'])) {
    $startPage = "../pages/branch/index.php";
}

if (isset($_SESSION['customer_id'])) {
    $startPage = "../pages/customer/index.php";
}
?>


<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    
    <title>Jollikod - Ikaw ang bida!</title>
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />

    <link rel="stylesheet" href="../assets/css/pages/index.css" />

    <script src="../assets/js/utils/content-loader.js" defer></script>
  </head>
  <body>
    <iframe id="app" src="<?= $startPage ?>" frameborder="0" class="frame"></iframe>
  </body>
</html>
