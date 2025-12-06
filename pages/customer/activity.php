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

// ensure customer is logged in
if (!isset($_SESSION['customer_id'])) {
  header("Location: ../customer/");
  exit;
}
$customer_id = $_SESSION['customer_id'];

// TAB state (default 1)
$tabNum = 1;
if (isset($_GET['tab']) && in_array((int)$_GET['tab'], [1,2,3,4,5])) {
  $tabNum = (int)$_GET['tab'];
}

// VIEW logic: view will be order_log_id
$view_log_id = isset($_GET['view']) && is_numeric($_GET['view']) ? (int)$_GET['view'] : null;

// ------------------- FETCH ALL ORDER LOGS FOR THIS CUSTOMER -------------------
// also fetch branch address so receipt can show "From (Branch)"
$sql = "
SELECT 
    ol.order_log_id,
    ol.order_id,
    ol.queued_at,
    ol.preparing_at,
    ol.delivering_at,
    ol.delivered_at,
    ol.received_at,
    o.created_at,
    o.item_quantity,
    o.destination_address,
    o.branch_id,
    b.name AS branch_name,
    b.address AS branch_address,
    p.subtotal,
    p.vat,
    p.delivery_fee,
    p.total,
    p.wallet_provider,
    p.status AS payment_status
FROM order_logs ol
JOIN orders o ON ol.order_id = o.order_id
JOIN branches b ON o.branch_id = b.branch_id
LEFT JOIN payments p ON o.order_id = p.order_id
WHERE o.customer_id = ?
ORDER BY o.created_at DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$customer_id]);
$all_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ------------------- CATEGORIZE INTO STAGES -------------------
$stages = [
  'queued' => [],
  'preparing' => [],
  'delivering' => [],
  'delivered' => [],
  'received' => []
];

foreach ($all_orders as $o) {
  if ($o['received_at']) $stages['received'][] = $o;
  elseif ($o['delivered_at']) $stages['delivered'][] = $o;
  elseif ($o['delivering_at']) $stages['delivering'][] = $o;
  elseif ($o['preparing_at']) $stages['preparing'][] = $o;
  elseif ($o['queued_at']) $stages['preparing'][] = $o; // queued moves to preparing bucket
  else $stages['queued'][] = $o;
}

// ------------------- LOAD SELECTED ORDER (for receipt) -------------------
$selectedOrder = null;
$orderItems = [];
if ($view_log_id) {
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
      LEFT JOIN menu_items mi ON oi.menu_item_id = mi.menu_item_id
      WHERE oi.order_id = ?
    ");
    $stmt->execute([$selectedOrder['order_id']]);
    $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Orders</title>

  <!-- reuse your page-specific css (you can create customer/activity.css if needed) -->
  <link rel="stylesheet" href="../../assets/css/pages/customer/activity.css" />
  <!-- receipt component already in your assets; it will style the receipt inside the layer -->
  <link rel="stylesheet" href="../../assets/css/components/receipt.css" />
  <script src="../../assets/js/components/layer.js" defer></script>

  <style>
    /* small inline styles for card layout — adjust or move to CSS file as you like */
    .orders-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 1rem;
    }

    .order-card {
      background: var(--neutral-100);
      border-radius: var(--radius-md);
      padding: var(--pad-md);
      box-shadow: var(--shadow-sm);
      display: flex;
      flex-direction: column;
      gap: .5rem;
      cursor: pointer;
      border: 1px solid rgba(0,0,0,0.03);
      transition: var(--transition-fast);
    }
    .order-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }

    .order-card .meta { display:flex; justify-content:space-between; align-items:center; gap:.5rem; }
    .order-card h4 { margin:0; font-size:1.05rem; }
    .order-card .status { font-weight:700; font-size:.9rem; color:var(--color-primary); }
    .order-card .small { font-size:.875rem; color:var(--neutral-400); }

    .order-card .row { display:flex; justify-content:space-between; align-items:center; gap:.5rem; }
    .order-card .row .left { color:var(--neutral-800); font-weight:600; }
    .order-card .row .right { color:var(--neutral-400); font-size:.9rem; }

    .no-orders {
      text-align:center;
      color:var(--neutral-400);
      padding:2rem 0;
    }

    /* keep receipt layer full width for customer */
    aside.layer #view-order .receipt { max-width:900px; margin: 0 auto; padding-bottom: 120px; }

    /* fixed bottom action area inside the receipt layer */
    .receipt-actions {
      position: sticky;
      bottom: 0;
      background: transparent;
      display:flex;
      gap: .75rem;
      justify-content: flex-end;
      padding: var(--pad-md) 0 0;
    }

    /* ensure the sticky total area (if present) doesn't cover content */
    @media (max-width: 900px) {
      .orders-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body class="customer">
  <main class="page">
    <header class="header header-page">
      <div class="context">
        <h1>Activity</h1>
      </div>
    </header>

    <main class="main-container main-scrollable">
      <main class="main">

        <div class="tab-container">
          <!-- radios -->
          <?php for ($i=1;$i<=5;$i++): ?>
            <input type="radio" name="tab-group" id="tab-<?= $i ?>" <?= $i === $tabNum ? 'checked' : '' ?> />
          <?php endfor; ?>

          <div class="tab-bar">
            <label for="tab-1" class="tab">Queued</label>
            <label for="tab-2" class="tab">Preparing</label>
            <label for="tab-3" class="tab">Delivering</label>
            <label for="tab-4" class="tab">Delivered</label>
            <label for="tab-5" class="tab">Received</label>
          </div>

          <?php
            $tabNames = ['queued','preparing','delivering','delivered','received'];
            $tabLabels = ['Queued','Preparing','Delivering','Delivered','Received'];
            foreach ($tabNames as $idx => $key):
              $orders = $stages[$key];
              $tabIndex = $idx + 1;
          ?>
          <div class="tab-content" id="content-<?= $tabIndex ?>">
            <div style="margin-bottom:1rem;">
              <p class="small">Showing <?= count($orders) ?> <?= strtolower($tabLabels[$idx]) ?> orders</p>
            </div>

            <?php if (count($orders) === 0): ?>
              <div class="no-orders">No <?= strtolower($tabLabels[$idx]) ?> orders</div>
            <?php else: ?>
              <div class="orders-grid">
                <?php foreach ($orders as $o): 
                  // compute friendly status
                  if ($o['received_at']) $friendly = 'Received';
                  elseif ($o['delivered_at']) $friendly = 'Delivered';
                  elseif ($o['delivering_at']) $friendly = 'Out for Delivery';
                  elseif ($o['preparing_at']) $friendly = 'Preparing';
                  elseif ($o['queued_at']) $friendly = 'Queued';
                  else $friendly = 'Queued';
                ?>
                  <!-- clicking the card opens the view layer via link with ?view=order_log_id&tab=current -->
                  <a href="?view=<?= $o['order_log_id'] ?>&tab=<?= $tabIndex ?>" class="order-card" title="View order <?= $o['order_id'] ?>">
                    <div class="meta">
                      <h4>Order #<?= htmlspecialchars($o['order_id']) ?></h4>
                      <div class="status"><?= htmlspecialchars($friendly) ?></div>
                    </div>

                    <div class="row">
                      <div class="left">Branch</div>
                      <div class="right"><?= htmlspecialchars($o['branch_name'] ?? 'Branch') ?></div>
                    </div>

                    <div class="row">
                      <div class="left">Placed</div>
                      <div class="right"><?= date('M d, Y h:i A', strtotime($o['created_at'])) ?></div>
                    </div>

                    <div class="row">
                      <div class="left">Items</div>
                      <div class="right"><?= (int)$o['item_quantity'] ?> pcs</div>
                    </div>

                    <div class="row">
                      <div class="left">Amount</div>
                      <div class="right">₱<?= number_format($o['total'] ?? 0, 2) ?></div>
                    </div>
                  </a>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>

        </div>
      </main>
    </main>
  </main>

  <!-- VIEW ORDER LAYER -->
  <aside class="layer <?= $selectedOrder ? 'layer-active' : '' ?>" id="view-order">
    <header class="header header-page">
      <div class="actions left">
        <button class="btn btn-secondary layer-close"><i class="bx bxs-dock-right-arrow btn-icon"></i></button>
      </div>
      <div class="context"><h1>Order Receipt</h1></div>
    </header>

    <main class="main-container main-scrollable">
      <main class="main">
        <?php if ($selectedOrder && !empty($orderItems)): ?>
          <section class="receipt">

            <!-- TOP CARD -->
            <div class="receipt-section highlight">
              <p class="status">
                <?php
                  if ($selectedOrder['received_at']) echo "Received";
                  elseif ($selectedOrder['delivered_at']) echo "Delivered";
                  elseif ($selectedOrder['delivering_at']) echo "Out for Delivery";
                  elseif ($selectedOrder['preparing_at']) echo "Preparing";
                  elseif ($selectedOrder['queued_at']) echo "Queued";
                  else echo "Queued";
                ?>
              </p>

              <h4>Order #<?= $selectedOrder['order_id'] ?></h4>
              <p class="date"><?= date('F d, Y h:i A', strtotime($selectedOrder['created_at'])) ?></p>
            </div>

            <!-- ORDER SECTION -->
            <div class="receipt-section">
              <div class="section-title">
                <h5>Order</h5>
                <div class="title-line"></div>
              </div>

              <div class="section-content">
                <?php foreach ($orderItems as $item): ?>
                <div class="item-row">
                  <span class="left"><?= (int)$item['quantity'] ?> <i class='bx  bxs-x'></i> <?= htmlspecialchars($item['item_name']) ?></span>
                  <span class="right">₱<?= number_format($item['total'], 2) ?></span>
                </div>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- DELIVERY SECTION -->
            <div class="receipt-section">
              <div class="section-title">
                <h5>Delivery</h5>
                <div class="title-line"></div>
              </div>

              <div class="section-content">
                <div class="label-row">
                  <label>From (Branch)</label>
                  <p><?= htmlspecialchars($selectedOrder['branch_address'] ?? $selectedOrder['branch_name'] ?? 'Branch Address') ?></p>
                </div>

                <div class="label-row">
                  <label>To (Customer)</label>
                  <p><?= htmlspecialchars($selectedOrder['destination_address'] ?? 'Pickup') ?></p>
                </div>
              </div>
            </div>

            <!-- PAYMENT SECTION -->
            <div class="receipt-section">
              <div class="section-title">
                <h5>Payment</h5>
                <div class="title-line"></div>
              </div>

              <div class="section-content">
                <div class="payment-row">
                  <span class="left">Subtotal</span>
                  <span class="right">₱<?= number_format($selectedOrder['subtotal'] ?? 0, 2) ?></span>
                </div>

                <div class="payment-row">
                  <span class="left">VAT</span>
                  <span class="right">₱<?= number_format($selectedOrder['vat'] ?? 0, 2) ?></span>
                </div>

                <div class="payment-row">
                  <span class="left">Delivery Fee</span>
                  <span class="right">₱<?= number_format($selectedOrder['delivery_fee'] ?? 0, 2) ?></span>
                </div>

                <div class="payment-row">
                  <span class="left">Wallet</span>
                  <span class="right"><?= htmlspecialchars($selectedOrder['wallet_provider'] ?? 'Cash / Other') ?></span>
                </div>

                <div class="payment-row">
                  <span class="left">Payment Status</span>
                  <span class="right"><?= htmlspecialchars(ucwords(strtolower($selectedOrder['payment_status'] ?? 'paid'))) ?></span>
                </div>
              </div>
            </div>

            <div class="receipt-section highlight">
              <p class="total">Total</p>
              <h4>₱<?= number_format($selectedOrder['total'] ?? 0, 2) ?></h4>
            </div>

            <div class="receipt-actions">
              <!-- Download placeholder -->
              <button class="btn btn-secondary" onclick="alert('Download not implemented yet')">Download</button>

              <!-- Feedback navigates to feedback.php with order_id -->
              <button class="btn btn-primary" onclick="window.location.href='./feedback.php?order_id=<?= $selectedOrder['order_id'] ?>'">Feedback</button>
            </div>

          </section>
        <?php else: ?>
          <p style="text-align:center; color:var(--neutral-400); padding:3rem 1rem;">Click an order card to view its receipt.</p>
        <?php endif; ?>
      </main>
    </main>
  </aside>

  <script>
    // Layer close button behavior: remove view param and keep tab state
    document.querySelectorAll(".layer-close").forEach(btn => {
      btn.addEventListener("click", () => {
        // remove view but preserve tab
        const tab = <?= json_encode($tabNum) ?>;
        // navigate to same page with tab only
        window.location.href = 'activity.php?tab=' + tab;
      });
    });
  </script>
</body>
</html>
