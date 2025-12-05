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

// ------------------- HANDLE NEXT STEP -------------------
if (isset($_GET['next']) && is_numeric($_GET['next'])) {
  $order_log_id = (int) $_GET['next'];

  $stmt = $pdo->prepare("
        SELECT ol.*, o.branch_id
        FROM order_logs ol
        JOIN orders o ON ol.order_id = o.order_id
        WHERE ol.order_log_id = ? AND o.branch_id = ?
    ");
  $stmt->execute([$order_log_id, $branch_id]);
  $log = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($log) {
    $update = '';
    if (!$log['queued_at'])
      $update = 'queued_at=NOW()';
    elseif (!$log['preparing_at'])
      $update = 'preparing_at=NOW()';
    elseif (!$log['delivering_at'])
      $update = 'delivering_at=NOW()';
    elseif (!$log['delivered_at'])
      $update = 'delivered_at=NOW()';
    elseif (!$log['received_at'])
      $update = 'received_at=NOW()';

    if ($update) {
      $pdo->prepare("UPDATE order_logs SET $update WHERE order_log_id=?")->execute([$order_log_id]);
    }
  }

  // Redirect back preserving view if open
  $redirect = 'activity.php';
  if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    $redirect .= '?view=' . (int) $_GET['view'];
  }
  header("Location: $redirect");
  exit;
}

// ------------------- FETCH ALL ORDER LOGS -------------------
$sql = "
SELECT 
    ol.order_log_id, ol.order_id, ol.queued_at, ol.preparing_at, ol.delivering_at, ol.delivered_at, ol.received_at,
    o.created_at, o.item_quantity, o.destination_address,
    c.name AS customer_name,
    p.subtotal, p.vat, p.delivery_fee, p.total
FROM order_logs ol
JOIN orders o ON ol.order_id=o.order_id
JOIN customers c ON o.customer_id=c.customer_id
JOIN payments p ON o.order_id=p.order_id
WHERE o.branch_id=?
ORDER BY o.created_at DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$branch_id]);
$all_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ------------------- CATEGORIZE STAGES -------------------
$stages = ['queued' => [], 'preparing' => [], 'delivering' => [], 'delivered' => [], 'received' => []];
foreach ($all_orders as $o) {
  if ($o['received_at'])
    $stages['received'][] = $o;
  elseif ($o['delivered_at'])
    $stages['delivered'][] = $o;
  elseif ($o['delivering_at'])
    $stages['delivering'][] = $o;
  elseif ($o['preparing_at'])
    $stages['preparing'][] = $o;
  elseif ($o['queued_at'])
    $stages['preparing'][] = $o;
  else
    $stages['queued'][] = $o;
}

// ------------------- LOAD SELECTED ORDER -------------------
$selectedOrder = null;
$orderItems = [];
if (isset($_GET['view']) && is_numeric($_GET['view'])) {
  $view_log_id = (int) $_GET['view'];
  foreach ($all_orders as $o) {
    if ($o['order_log_id'] == $view_log_id) {
      $selectedOrder = $o;
      break;
    }
  }

  if ($selectedOrder) {
    $stmt = $pdo->prepare("
            SELECT oi.quantity, oi.unit_price, oi.total, mi.name AS item_name
            FROM order_items oi
            JOIN menu_items mi ON oi.menu_item_id = mi.menu_item_id
            WHERE oi.order_id=?
        ");
    $stmt->execute([$selectedOrder['order_id']]);
    $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}

// ------------------- TAB STATE -------------------
$tabNum = 1;
if (isset($_GET['tab']) && in_array((int) $_GET['tab'], [1, 2, 3, 4, 5]))
  $tabNum = (int) $_GET['tab'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Activity</title>
  <link rel="stylesheet" href="../../assets/css/pages/branch/activity.css">
  <script src="../../assets/js/components/layer.js" defer></script>
</head>

<body class="branch">
  <main class="page">
    <header class="header header-page">
      <div class="context">
        <h1>Activity</h1>
      </div>
    </header>

    <main class="main-container main-scrollable">
      <main class="main">
        <div class="tab-container">
          <?php for ($i = 1; $i <= 5; $i++): ?>
            <input type="radio" name="tab-group" id="tab-<?= $i ?>" <?= $i == $tabNum ? 'checked' : '' ?>>
          <?php endfor; ?>
          <div class="tab-bar">
            <label for="tab-1" class="tab">Queued</label>
            <label for="tab-2" class="tab">Preparing</label>
            <label for="tab-3" class="tab">Delivering</label>
            <label for="tab-4" class="tab">Delivered</label>
            <label for="tab-5" class="tab">Received</label>
          </div>

          <?php
          $tabNames = ['queued', 'preparing', 'delivering', 'delivered', 'received'];
          $tabLabels = ['Queued', 'Preparing', 'Delivering', 'Delivered', 'Received'];
          foreach ($tabNames as $idx => $key):
            $orders = $stages[$key];
            $tabIndex = $idx + 1;
            ?>
            <div class="tab-content" id="content-<?= $tabIndex ?>">
              <div class="table-container">
                <div class="table-header">
                    <form class="table-filter">
                      <div class="field">
                        <label>Date</label>
                        <input type="date" />
                      </div>

                      <div class="field">
                        <label>Sort By</label>
                        <select>
                          <option value="date">ID</option>
                          <option value="name">Name</option>
                        </select>
                      </div>

                      <button class="btn btn-primary">
                        <span class="btn-label">Apply</span>
                        <i class="bx bxs-user-plus btn-icon"></i>
                      </button>
                    </form>
                  </div>
                  
                <div class="table-content">
                  <table>
                    <thead>
                      <tr>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Qty</th>
                        <th>Address</th>
                        <th>Amount</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($orders as $o): ?>
                        <tr>
                          <td><?= date('M d, Y h:i A', strtotime($o['created_at'])) ?></td>
                          <td><?= htmlspecialchars($o['customer_name']) ?></td>
                          <td><?= $o['item_quantity'] ?></td>
                          <td><?= htmlspecialchars($o['destination_address'] ?? 'Pickup') ?></td>
                          <td>₱<?= number_format($o['total'], 2) ?></td>
                          <td class="actions">
                            <!-- View opens layer for THIS order_log -->
                            <a href="?view=<?= $o['order_log_id'] ?>&tab=<?= $tabIndex ?>"
                              class="btn btn-primary layer-open">View</a>
                            <?php if ($key !== 'received'): ?>
                              <a href="?next=<?= $o['order_log_id'] ?>&view=<?= $selectedOrder ? $selectedOrder['order_log_id'] : '' ?>&tab=<?= $tabIndex ?>"
                                class="btn btn-secondary">Next</a>
                            <?php endif; ?>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                      <?php if (empty($orders)): ?>
                        <tr>
                          <td colspan="6">No <?= strtolower($tabLabels[$idx]) ?>
                            orders</td>
                        </tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </main>
    </main>
  </main>

  <!-- Layer Panel -->
  <aside class="layer <?= $selectedOrder ? 'layer-active' : '' ?>" id="view-order">
    <header class="header header-page">
      <div class="actions left">
        <button class="btn btn-secondary layer-close"><i class="bx bxs-dock-right-arrow btn-icon"></i></button>
      </div>
      <div class="context">
        <h1>Order Receipt</h1>
      </div>
    </header>
    <main class="main-container main-scrollable">
      <main class="main">
        <?php if ($selectedOrder && !empty($orderItems)): ?>
          <div style="padding:20px; background:#fff; border-radius:8px;">
            <h2 style="text-align:center; color:#e31837;">JOLLIKOD</h2>
            <p><strong>Order #<?= $selectedOrder['order_id'] ?></strong></p>
            <p><strong>Customer:</strong> <?= htmlspecialchars($selectedOrder['customer_name']) ?></p>
            <p><strong>Date:</strong> <?= date('F d, Y h:i A', strtotime($selectedOrder['created_at'])) ?></p>
            <p><strong>Delivery:</strong> <?= htmlspecialchars($selectedOrder['destination_address'] ?? 'Pickup') ?></p>
            <hr style="border:1px dashed #ccc; margin:15px 0;">
            <?php foreach ($orderItems as $item): ?>
              <div style="display:flex; justify-content:space-between; margin:5px 0;">
                <span><?= htmlspecialchars($item['item_name']) ?> × <?= $item['quantity'] ?></span>
                <span>₱<?= number_format($item['total'], 2) ?></span>
              </div>
            <?php endforeach; ?>
            <hr style="border:1px dashed #ccc; margin:15px 0;">
            <div style="display:flex; justify-content:space-between;">
              <span>Subtotal</span><span>₱<?= number_format($selectedOrder['subtotal'], 2) ?></span></div>
            <div style="display:flex; justify-content:space-between;">
              <span>VAT</span><span>₱<?= number_format($selectedOrder['vat'], 2) ?></span></div>
            <div style="display:flex; justify-content:space-between;"><span>Delivery
                Fee</span><span>₱<?= number_format($selectedOrder['delivery_fee'], 2) ?></span></div>
            <hr style="border-top:3px double #000; margin:15px 0;">
            <div style="display:flex; justify-content:space-between; font-weight:bold; font-size:1.3em;">
              <span>TOTAL</span>
              <span style="color:#e31837;">₱<?= number_format($selectedOrder['total'], 2) ?></span>
            </div>
          </div>
        <?php else: ?>
          <p style="text-align:center; color:#999; padding:50px 20px;">Click "View" on any order to see receipt</p>
        <?php endif; ?>
      </main>
    </main>
  </aside>

  <script>
    // Layer close
    document.querySelectorAll(".layer-close").forEach(btn => {
      btn.addEventListener("click", () => {
        btn.closest(".layer").classList.remove("layer-active");
        window.location.href = 'activity.php?tab=<?= $tabNum ?>';
      });
    });
  </script>
</body>

</html>