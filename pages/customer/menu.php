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
$customer_id = (int) $_SESSION['customer_id'];

// === CONFIG ===
$DEFAULT_BRANCH = 1;
$VAT_RATE = 0.12;
$DELIVERY_FEE = 49.00;

function redirect_with_msg($url, $msg = null)
{
  if ($msg) {
    $_SESSION['flash_message'] = $msg;
  }
  header("Location: $url");
  exit;
}

// === Fetch customer data (fresh from DB) ===
$stmt = $pdo->prepare("SELECT * FROM customers WHERE customer_id = ? AND deleted_at IS NULL LIMIT 1");
$stmt->execute([$customer_id]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

// === Determine selected branch ===
// Accept GET branch param (from dropdown) or default
$selected_branch = isset($_GET['branch']) && is_numeric($_GET['branch']) ? (int) $_GET['branch'] : $DEFAULT_BRANCH;

// Validate branch exists
$stmt = $pdo->prepare("SELECT * FROM branches WHERE branch_id = ? AND deleted_at IS NULL LIMIT 1");
$stmt->execute([$selected_branch]);
$branch = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$branch) {
  // fallback
  $selected_branch = $DEFAULT_BRANCH;
  $stmt = $pdo->prepare("SELECT * FROM branches WHERE branch_id = ? AND deleted_at IS NULL LIMIT 1");
  $stmt->execute([$selected_branch]);
  $branch = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$branch) {
    die("No branch available.");
  }
}

// === Load the menu for this branch ===
// Get menu_id for branch (menus table)
$stmt = $pdo->prepare("SELECT menu_id FROM menus WHERE branch_id = ? LIMIT 1");
$stmt->execute([$selected_branch]);
$menuRow = $stmt->fetch(PDO::FETCH_ASSOC);
$menu_id = $menuRow ? (int) $menuRow['menu_id'] : null;

// Load categories (menu_categories always global)
$categories = [];
$stmt = $pdo->query("SELECT menu_category_id, name FROM menu_categories ORDER BY menu_category_id ASC");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
  $categories[(int) $r['menu_category_id']] = $r['name'];
}

// Load items for this menu (if menu exists)
$items = [];
if ($menu_id) {
  $stmt = $pdo->prepare("SELECT mi.*, mc.name AS category_name FROM menu_items mi JOIN menu_categories mc ON mi.menu_category_id = mc.menu_category_id WHERE mi.menu_id = ? AND mi.deleted_at IS NULL ORDER BY mi.created_at DESC");
  $stmt->execute([$menu_id]);
  $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Load inventories for these items (map by menu_item_id)
$inventories = [];
if ($menu_id) {
  $stmt = $pdo->prepare("SELECT * FROM menu_inventories WHERE menu_id = ?");
  $stmt->execute([$menu_id]);
  foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $inv) {
    $inventories[(int) $inv['menu_item_id']] = $inv;
  }
}

// Flash message (from redirect)
$flash = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']);

// === SERVER-SIDE CHECKOUT HANDLER (no AJAX) ===
$errors = [];
$successOrderId = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'checkout') {
  // Expect cart_data (JSON) and destination_address field
  $cart_json = $_POST['cart_data'] ?? '';
  $destination_address = trim($_POST['destination_address'] ?? '');
  // Basic validation
  if (!$cart_json) {
    $errors[] = "Cart is empty.";
  } else {
    $cart = json_decode($cart_json, true);
    if (!$cart || !isset($cart['branch']) || !isset($cart['items']) || !is_array($cart['items'])) {
      $errors[] = "Invalid cart data.";
    } else {
      // Ensure branch in cart matches selected branch
      $cart_branch = (int) $cart['branch'];
      if ($cart_branch !== $selected_branch) {
        $errors[] = "Cart branch does not match selected branch. Please switch branch or clear cart.";
      }
      // Ensure destination address (delivery required) if none then use customer's address
      if (empty($destination_address)) {
        $destination_address = $customer['address'] ?? '';
      }
      if (empty($destination_address)) {
        $errors[] = "Please provide a delivery address.";
      }
    }
  }

  // If no errors, proceed to validate inventory and totals
  if (empty($errors)) {
    // Build items list and compute totals server-side (to avoid tampering)
    $order_items = []; // each: menu_item_id, quantity, unit_price, total
    $subtotal = 0.0;
    // We'll also need the menu_id for the branch (we already have $menu_id) - ensure present
    if (!$menu_id) {
      $errors[] = "Menu not found for selected branch.";
    } else {
      foreach ($cart['items'] as $mid => $entry) {
        $menu_item_id = (int) $mid;
        $qty = max(1, (int) ($entry['qty'] ?? 1));
        // Fetch item price & details directly from DB (prevent client tampering)
        $stmt = $pdo->prepare("SELECT menu_item_id, name, price, menu_id FROM menu_items WHERE menu_item_id = ? AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([$menu_item_id]);
        $dbItem = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$dbItem) {
          $errors[] = "Menu item #$menu_item_id not found.";
          break;
        }
        if ((int) $dbItem['menu_id'] !== $menu_id) {
          $errors[] = "Menu item #$menu_item_id does not belong to selected branch.";
          break;
        }
        $unit_price = (float) $dbItem['price'];
        $total_price = round($unit_price * $qty, 2);
        $order_items[] = [
          'menu_item_id' => $menu_item_id,
          'name' => $dbItem['name'],
          'quantity' => $qty,
          'unit_price' => $unit_price,
          'total' => $total_price
        ];
        $subtotal += $total_price;
      }
    }
    // compute vat and totals
    $vat = round($subtotal * $VAT_RATE, 2);
    $delivery_fee = $DELIVERY_FEE;
    $total = round($subtotal + $vat + $delivery_fee, 2);

    // Inventory checks (ensure enough stock in menu_inventories)
    if (empty($errors)) {
      foreach ($order_items as $oi) {
        $mid = $oi['menu_item_id'];
        $stmt = $pdo->prepare("SELECT menu_inventory_id, stock_quantity FROM menu_inventories WHERE menu_item_id = ? AND menu_id = ? LIMIT 1");
        $stmt->execute([$mid, $menu_id]);
        $inv = $stmt->fetch(PDO::FETCH_ASSOC);
        $avail = $inv ? (int) $inv['stock_quantity'] : 0;
        if ($avail < $oi['quantity']) {
          $errors[] = "Insufficient stock for '{$oi['name']}'. Available: {$avail}, requested: {$oi['quantity']}.";
          break;
        }
      }
    }

    // If still OK, perform DB inserts inside a transaction
    if (empty($errors)) {
      try {
        $pdo->beginTransaction();

        // 1) Insert into orders
        $stmt = $pdo->prepare("INSERT INTO orders (branch_id, customer_id, item_quantity, store_address, destination_address, status) VALUES (?, ?, ?, ?, ?, ?)");
        $total_qty = array_sum(array_column($order_items, 'quantity'));
        $store_address = $branch['address'] ?? '';
        $status = 'placed';
        $stmt->execute([$selected_branch, $customer_id, $total_qty, $store_address, $destination_address, $status]);
        $order_id = (int) $pdo->lastInsertId();

        if (!$order_id) {
          throw new Exception("Failed to create order.");
        }

        // 2) Insert order_items
        $stmtInsertItem = $pdo->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity, total, unit_price) VALUES (?, ?, ?, ?, ?)");
        foreach ($order_items as $oi) {
          $stmtInsertItem->execute([$order_id, $oi['menu_item_id'], $oi['quantity'], $oi['total'], $oi['unit_price']]);
        }

        // 3) Insert payments
        $stmtPay = $pdo->prepare("INSERT INTO payments (order_id, subtotal, vat, delivery_fee, total, wallet_provider, wallet_account, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        // For now we mark as paid, wallet_provider empty
        $payment_status = 'paid';
        $stmtPay->execute([$order_id, $subtotal, $vat, $delivery_fee, $total, null, null, $payment_status]);

        // 4) Insert order_logs (initially empty timestamps)
        $stmtLog = $pdo->prepare("INSERT INTO order_logs (order_id) VALUES (?)");
        $stmtLog->execute([$order_id]);

        // 5) Update menu_inventories (decrement)
        $stmtUpdateInv = $pdo->prepare("UPDATE menu_inventories SET stock_quantity = stock_quantity - ?, updated_at = NOW() WHERE menu_inventory_id = ?");
        foreach ($order_items as $oi) {
          // fetch inventory row id
          $stmt = $pdo->prepare("SELECT menu_inventory_id, stock_quantity FROM menu_inventories WHERE menu_item_id = ? AND menu_id = ? LIMIT 1 FOR UPDATE");
          $stmt->execute([$oi['menu_item_id'], $menu_id]);
          $inv = $stmt->fetch(PDO::FETCH_ASSOC);
          if (!$inv) {
            // if no inventory record, treat as zero available
            throw new Exception("Inventory record missing for item {$oi['menu_item_id']}.");
          }
          $newQty = (int) $inv['stock_quantity'] - (int) $oi['quantity'];
          if ($newQty < 0) {
            throw new Exception("Insufficient stock while committing for item {$oi['menu_item_id']}.");
          }
          $stmtUpdateInv->execute([(int) $oi['quantity'], $inv['menu_inventory_id']]);
        }

        $pdo->commit();
        // success -> redirect to ?order=order_id so the layer will open
        $successOrderId = $order_id;
        redirect_with_msg("menu.php?branch={$selected_branch}&order={$order_id}", "Order placed.");
      } catch (Exception $ex) {
        $pdo->rollBack();
        $errors[] = "Checkout failed: " . $ex->getMessage();
      }
    }
  }
}

// === If viewing a completed placed order to render the order-placed layer ===
$placedOrder = null;
$placedOrderItems = [];
if (isset($_GET['order']) && is_numeric($_GET['order'])) {
  $oid = (int) $_GET['order'];
  // fetch order and payment
  $stmt = $pdo->prepare("
        SELECT o.*, p.subtotal, p.vat, p.delivery_fee, p.total, p.status AS payment_status
        FROM orders o
        LEFT JOIN payments p ON o.order_id = p.order_id
        WHERE o.order_id = ? AND o.customer_id = ?
        LIMIT 1
    ");
  $stmt->execute([$oid, $customer_id]);
  $placedOrder = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($placedOrder) {
    // items
    $stmt = $pdo->prepare("
            SELECT oi.*, mi.name AS item_name
            FROM order_items oi
            LEFT JOIN menu_items mi ON oi.menu_item_id = mi.menu_item_id
            WHERE oi.order_id = ?
        ");
    $stmt->execute([$oid]);
    $placedOrderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } else {
    // no permission or not found - ignore
  }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Find your Cravings!</title>

  <link rel="stylesheet" href="../../assets/css/pages/customer/menu.css" />
  <script src="../../assets/js/components/layer.js" defer></script>
</head>

<body class="customer">
  <main class="page">
    <header class="header header-page">
      <div class="context">
        <div class="input-container">
          <select id="branchSelect">
            <?php
            // show all branches
            $stmt = $pdo->query("SELECT branch_id, name FROM branches WHERE deleted_at IS NULL ORDER BY branch_id ASC");
            $allBranches = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($allBranches as $b):
              ?>
              <option value="<?= (int) $b['branch_id'] ?>" <?= (int) $b['branch_id'] === $selected_branch ? 'selected' : '' ?>>
                <?= htmlspecialchars($b['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="actions right">
        <button class="btn btn-primary layer-open" data-layer-target="basket">
          <span class="btn-label">My Basket</span>
          <i class="bx bxs-basket btn-icon"></i>
          <span id="basketCount" class="basket-count">0</span>
        </button>
      </div>
    </header>

    <main class="main-container main-scrollable">
      <main class="main">
        <section class="menu-container">
          <header class="menu-header">
            <div class="category-list" id="categoryList">
              <div class="cat-chip active" data-cat="all">All</div>
              <?php foreach ($categories as $cid => $cname): ?>
                <div class="cat-chip" data-cat="<?= $cid ?>"><?= htmlspecialchars($cname) ?></div>
              <?php endforeach; ?>
            </div>
            <div>
              <input id="searchInput" placeholder="Search menu..."
                style="width:100%; padding:.6rem; border-radius:8px; border:2px solid var(--neutral-200)" />
            </div>
          </header>

          <!-- Menu grid -->
          <div id="menuGrid" class="menu-grid">
            <?php if (empty($items)): ?>
              <div style="grid-column:1/-1; text-align:center; color:var(--neutral-400);">No items available for this
                branch.</div>
            <?php else: ?>
              <?php foreach ($items as $it):
                $mid = (int) $it['menu_item_id'];
                $img = '../../assets/res' . $it['image_path'];
                $desc = $it['description'] ?? '';
                $stockQty = isset($inventories[$mid]) ? (int) $inventories[$mid]['stock_quantity'] : 0;
                ?>
                <div class="menu-card" data-id="<?= $mid ?>" data-cat="<?= (int) $it['menu_category_id'] ?>">
                  <button class="btn btn-secondary add-btn"
                    onclick="event.stopPropagation(); addToCartImmediate(<?= $mid ?>)"><i
                      class="bx bxs-cart-add"></i></button>
                  <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($it['name']) ?>" loading="lazy" />
                  <div style="display:flex; flex-direction:column; gap:.3rem;">
                    <div class="meta">
                      <strong style="font-size:1rem;"><?= htmlspecialchars($it['name']) ?></strong>
                      <span style="font-weight:800;">₱<?= number_format($it['price'], 2) ?></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                      <div class=""><?= htmlspecialchars($it['category_name']) ?></div>
                      <div class=""><?= $stockQty > 0 ? "{$stockQty} pcs" : "Out of stock" ?></div>
                    </div>
                    <p class=""
                      style="margin-top:.4rem; max-height:3rem; overflow:hidden; text-overflow:ellipsis;">
                      <?= htmlspecialchars($desc) ?></p>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </section>
      </main>
    </main>
  </main>

  <!-- BASKET LAYER -->
  <aside class="layer" id="basket">
    <header class="header header-page">
      <div class="actions left">
        <button class="btn btn-secondary layer-close" title="Close Panel"><i
            class="bx bxs-dock-right-arrow btn-icon"></i></button>
      </div>
      <div class="context">
        <h1>My Basket</h1>
      </div>
    </header>

    <main class="main-container main-scrollable">
      <main class="main">

        <section class="receipt">

            <div class="receipt-section highlight">
              <p class="status" id="basketErrors"></p>
            </div>

            <div class="receipt-section">
              <div id="basketContent">
                <div class="">
                  <div class="receipt-section">
                    <div class="section-title">
                      <h5>Order</h5>
                      <div class="title-line"></div>
                    </div>
                    <div class="section-content">
                      Loading basket...
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <form class="receipt-section" id="checkoutForm" method="post">

                <input type="hidden" name="action" value="checkout" />
                <input type="hidden" name="cart_data" id="cart_data" value="" />

                <div class="receipt-section">
                  <div class="section-title">
                    <h5>Delivery</h5>
                    <div class="title-line"></div>
                  </div>
                  <div class="section-content">
                    <div class="account-field">
                        <label for="destination_address">Address</label>
                      <div class="input-container">
                        <input type="text" name="destination_address" id="destination_address"
                          value="<?= htmlspecialchars($customer['address'] ?? '') ?>" placeholder="Enter delivery address"
                          required />
                        <i class="bx bxs-location"></i>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="receipt-section">
                  <button class="btn btn-secondary layer-close">Cancel</button>
                  <button class="btn btn-primary" id="placeOrderBtn">Place Order</button>
                </div>

              </form>

          </section>  

      </main>
    </main>
  </aside>

  <!-- VIEW ITEM LAYER -->
  <aside class="layer" id="view-item">
    <header class="header header-page">
      <div class="actions left"><button class="btn btn-secondary layer-close" title="Close Panel"><i
            class="bx bxs-dock-right-arrow btn-icon"></i></button></div>
      <div class="context">
        <h1 id="viewItemTitle">Item</h1>
      </div>
    </header>

    <main class="main-container main-scrollable">
      <main class="main">
        <div id="viewItemContent">
        </div>
      </main>
    </main>
  </aside>

  <!-- ORDER PLACED LAYER -->
  <aside class="layer <?= $placedOrder ? 'layer-active' : '' ?>" id="order-placed">
    <header class="header header-page">
      <div class="actions left"><button class="btn btn-secondary layer-close" title="Close Panel"><i
            class="bx bxs-dock-right-arrow btn-icon"></i></button></div>
      <div class="context">
        <h1>Order Placed</h1>
      </div>
    </header>

    <main class="main-container main-scrollable">
      <main class="main">
        <?php if ($placedOrder): ?>
          <section class="receipt">

            <div class="receipt-section highlight">
              <p class="status"><?= htmlspecialchars(ucfirst($placedOrder['status'] ?? 'placed')) ?></p>
              <h4>Order #<?= $placedOrder['order_id'] ?></h4>
              <p class="date"><?= date('F d, Y h:i A', strtotime($placedOrder['created_at'])) ?></p>
            </div>

            <div class="receipt-section">
              <div class="section-title">
                <h5>Order</h5>
                <div class="title-line"></div>
              </div>
              <div class="section-content">
                <?php foreach ($placedOrderItems as $item): ?>
                  <div class="item-row">
                    <span class="left"><?= (int) $item['quantity'] ?><i class='bx  bxs-x'></i><?= htmlspecialchars($item['item_name']) ?></span>
                    <span class="right">₱<?= number_format($item['total'], 2) ?></span>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>

            <div class="receipt-section">
              <div class="section-title">
                <h5>Delivery</h5>
                <div class="title-line"></div>
              </div>
              <div class="section-content">
                <div class="label-row"><label>From (Branch)</label>
                  <p><?= htmlspecialchars($branch['address'] ?? '') ?></p>
                </div>
                <div class="label-row"><label>To (Customer)</label>
                  <p><?= htmlspecialchars($placedOrder['destination_address'] ?? '') ?></p>
                </div>
              </div>
            </div>

            <div class="receipt-section">
              <div class="section-title">
                <h5>Payment</h5>
                <div class="title-line"></div>
              </div>
              <div class="section-content">
                <div class="payment-row"><span class="left">Subtotal</span><span
                    class="right">₱<?= number_format($placedOrder['subtotal'], 2) ?></span></div>
                <div class="payment-row"><span class="left">VAT (12%)</span><span
                    class="right">₱<?= number_format($placedOrder['vat'], 2) ?></span></div>
                <div class="payment-row"><span class="left">Delivery Fee</span><span
                    class="right">₱<?= number_format($placedOrder['delivery_fee'], 2) ?></span></div>
                <div class="payment-row"><span class="left">TOTAL</span><span
                    class="right">₱<?= number_format($placedOrder['total'], 2) ?></span></div>
              </div>
            </div>

            <div class="receipt-section">
              <button class="btn btn-secondary">Download</button>
              <button class="btn btn-primary" onclick="parent.navigate('./activity.php')">Track Order</button>
            </div>
          </section>
        <?php else: ?>
          <p>No placed order found.</p>
        <?php endif; ?>
      </main>
    </main>
  </aside>

  <script>
    /* ============================
       Client-side cart & UI logic
       ============================ */

    const CART_KEY = 'jollikod_cart_v1';
    const VAT_RATE = <?= json_encode($VAT_RATE) ?>;
    const DELIVERY_FEE = <?= json_encode($DELIVERY_FEE) ?>;
    const SELECTED_BRANCH = <?= json_encode($selected_branch) ?>;

    function readCart() {
      try {
        const raw = localStorage.getItem(CART_KEY);
        if (!raw) return null;
        const parsed = JSON.parse(raw);
        return parsed;
      } catch (e) {
        console.warn('Invalid cart JSON', e);
        return null;
      }
    }

    function writeCart(cart) {
      localStorage.setItem(CART_KEY, JSON.stringify(cart));
      updateBasketCountUI();
    }
    function clearCart() {
      localStorage.removeItem(CART_KEY);
      updateBasketCountUI();
    }

    /* Ensure cart branch aligns: when branch change, prompt to clear */
    document.querySelector('#branchSelect').addEventListener('change', (e) => {
      const newBranch = parseInt(e.target.value, 10);
      const cart = readCart();
      if (cart && cart.branch && cart.branch !== newBranch) {
        if (!confirm("This action will clear your basket. Do you want to continue?")) {
          // revert selection
          e.target.value = SELECTED_BRANCH;
          return;
        }
        clearCart();
      }
      // navigate to selected branch (full page reload)
      window.location.href = `?branch=${newBranch}`;
    });

    /* Category filtering */
    let activeCategory = 'all';
    document.querySelectorAll('.cat-chip').forEach(chip => {
      chip.addEventListener('click', (ev) => {
        document.querySelectorAll('.cat-chip').forEach(c => c.classList.remove('active'));
        chip.classList.add('active');
        activeCategory = chip.dataset.cat;
        renderMenuGrid();
      });
    });

    /* Search */
    document.querySelector('#searchInput').addEventListener('input', debounce(() => {
      renderMenuGrid();
    }, 200));

    function renderMenuGrid() {
      const q = document.querySelector('#searchInput').value.trim().toLowerCase();
      const cards = Array.from(document.querySelectorAll('.menu-card'));
      cards.forEach(card => {
        const cat = card.dataset.cat;
        const id = card.dataset.id;
        const title = card.querySelector('strong').textContent.toLowerCase();
        const desc = card.querySelector('p') ? card.querySelector('p').textContent.toLowerCase() : '';
        let show = true;
        if (activeCategory !== 'all' && activeCategory !== String(cat)) show = false;
        if (q && !(title.includes(q) || desc.includes(q))) show = false;
        card.style.display = show ? 'flex' : 'none';
      });
    }

    /* Utility debounce */
    function debounce(fn, wait) {
      let t;
      return function (...a) {
        clearTimeout(t);
        t = setTimeout(() => fn.apply(this, a), wait);
      }
    }

    /* Add to cart immediately (small button). Uses O(1) key-value structure. */
    function addToCartImmediate(menu_item_id) {
      // find card to extract data
      const card = document.querySelector(`.menu-card[data-id="${menu_item_id}"]`);
      if (!card) return alert("Item not found");
      const name = card.querySelector('strong').textContent.trim();
      const priceText = card.querySelector('.meta span').textContent.replace('₱', '').trim();
      const price = parseFloat(priceText.replace(/,/g, ''));
      const img = card.querySelector('img') ? card.querySelector('img').getAttribute('src') : '';

      let cart = readCart() || { branch: SELECTED_BRANCH, items: {} };
      if (cart.branch !== SELECTED_BRANCH) {
        if (!confirm("Your basket belongs to another branch and will be cleared. Continue?")) return;
        cart = { branch: SELECTED_BRANCH, items: {} };
      }

      const id = String(menu_item_id);
      if (!cart.items[id]) {
        cart.items[id] = { menu_item_id: menu_item_id, name: name, price: price, qty: 1, image: img };
      } else {
        cart.items[id].qty = (cart.items[id].qty || 0) + 1;
      }
      writeCart(cart);
      flashToast(`${name} added to basket`);
    }

    /* Open item view when clicking card */
    document.querySelectorAll('.menu-card').forEach(card => {
      card.addEventListener('click', (ev) => {
        // if clicked the small add button, stopPropagation prevented earlier
        const id = card.dataset.id;
        openViewItem(id);
      });
    });

    function openViewItem(menu_item_id) {
      // build view content from page elements (no extra ajax)
      const card = document.querySelector(`.menu-card[data-id="${menu_item_id}"]`);
      if (!card) return;
      const name = card.querySelector('strong').textContent.trim();
      const priceText = card.querySelector('.meta span').textContent.replace('₱', '').trim();
      const price = parseFloat(priceText.replace(/,/g, ''));
      const img = card.querySelector('img') ? card.querySelector('img').getAttribute('src') : '';
      const desc = card.querySelector('p') ? card.querySelector('p').textContent : '';

      const content = document.getElementById('viewItemContent');
      document.getElementById('viewItemTitle').textContent = name;
      content.innerHTML = `
    <div style="display:flex; gap:1rem; align-items:flex-start; flex-direction:column;">
      <img src="${escapeHtml(img)}" alt="${escapeHtml(name)}" style="width:100%;max-height:260px;object-fit:cover;border-radius:8px;" />
      <div>
        <h3 style="margin:0 0 .4rem;">${escapeHtml(name)}</h3>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.6rem;">
          <div class="">${card.querySelector('.') ? card.querySelector('.').textContent : ''}</div>
          <div style="font-weight:800;">₱${price.toFixed(2)}</div>
        </div>
        <p class="" style="margin-bottom:.8rem;">${escapeHtml(desc)}</p>

        <div style="display:flex;gap:.6rem; align-items:center; margin-bottom:.6rem;">
          <label style="font-weight:700;">Quantity</label>
          <div style="display:flex;align-items:center; gap:.4rem;">
            <button class="btn btn-secondary" id="viewQtyMinus">-</button>
            <input id="viewQty" type="number" value="1" min="1" style="width:64px;padding:.35rem;border-radius:6px;border:1px solid var(--neutral-200);" />
            <button class="btn btn-secondary" id="viewQtyPlus">+</button>
          </div>
        </div>

        <div style="display:flex; gap:.6rem;">
          <button class="btn btn-secondary layer-close">Cancel</button>
          <button class="btn btn-primary" id="viewAddBtn">Add to Basket</button>
        </div>
      </div>
    </div>
  `;
      // wire up qty buttons
      document.getElementById('viewQtyPlus').addEventListener('click', () => {
        const el = document.getElementById('viewQty'); el.value = parseInt(el.value || '1') + 1;
      });
      document.getElementById('viewQtyMinus').addEventListener('click', () => {
        const el = document.getElementById('viewQty'); el.value = Math.max(1, parseInt(el.value || '1') - 1);
      });
      document.getElementById('viewAddBtn').addEventListener('click', (ev) => {
        const qty = Math.max(1, parseInt(document.getElementById('viewQty').value || '1'));
        addToCartFromView(menu_item_id, name, price, img, qty);
        // close layer
        document.getElementById('view-item').classList.remove('layer-active');
      });

      // open layer
      document.getElementById('view-item').classList.add('layer-active');
    }

    /* Add from view with specified qty */
    function addToCartFromView(menu_item_id, name, price, img, qty) {
      let cart = readCart() || { branch: SELECTED_BRANCH, items: {} };
      if (cart.branch !== SELECTED_BRANCH) {
        if (!confirm("Your basket belongs to another branch and will be cleared. Continue?")) return;
        cart = { branch: SELECTED_BRANCH, items: {} };
      }
      const id = String(menu_item_id);
      if (!cart.items[id]) {
        cart.items[id] = { menu_item_id: menu_item_id, name: name, price: price, qty: qty, image: img };
      } else {
        cart.items[id].qty = (cart.items[id].qty || 0) + qty;
      }
      writeCart(cart);
      flashToast(`${name} added to basket`);
    }

    /* Update basket UI */
    function updateBasketCountUI() {
      const bc = document.getElementById('basketCount');
      const cart = readCart();
      let totalCount = 0;
      if (cart && cart.items) {
        for (const k in cart.items) totalCount += Number(cart.items[k].qty || 0);
      }
      if (totalCount > 0) {
        bc.style.display = 'inline-block';
        bc.textContent = totalCount;
      } else {
        bc.style.display = 'none';
      }
    }

    /* Render basket content inside basket layer */
    function renderBasketLayer() {
      const container = document.getElementById('basketContent');
      const cart = readCart();
      if (!cart || !cart.items || Object.keys(cart.items).length === 0) {
        container.innerHTML = "<p class=''>Your basket is empty.</p>";
        return;
      }
      // build UI: list items, qty controls, totals
      let html = '<div style="display:flex;flex-direction:column;gap:.6rem;">';
      for (const k of Object.keys(cart.items)) {
        const it = cart.items[k];
        html += `
      <div style="display:flex;justify-content:space-between;align-items:center;gap:.6rem;padding:.6rem;border-radius:8px;background:var(--neutral-100);">
        <div style="display:flex;gap:.6rem;align-items:center;">
          <img src="${escapeHtml('../../assets' + it.image)}" style="width:56px;height:56px;object-fit:cover;border-radius:8px;" />
          <div style="display:flex;flex-direction:column;">
            <strong style="font-size:1rem;">${escapeHtml(it.name)}</strong>
            <div class="">₱${Number(it.price).toFixed(2)}</div>
          </div>
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:.4rem;">
          <div style="display:flex;align-items:center;gap:.4rem;">
            <button class="btn btn-secondary" onclick="changeQty('${k}', -1)">-</button>
            <input type="number" id="qty_${k}" value="${parseInt(it.qty)}" min="1" style="width:64px;padding:.25rem;border-radius:6px;border:1px solid var(--neutral-200);" />
            <button class="btn btn-secondary" onclick="changeQty('${k}', 1)">+</button>
          </div>
          <div style="display:flex;gap:.4rem;">
            <button class="btn btn-danger" onclick="removeItem('${k}')">Remove</button>
            <div style="font-weight:800;">₱${(Number(it.price) * Number(it.qty)).toFixed(2)}</div>
          </div>
        </div>
      </div>`;
      }
      // totals
      const subtotal = computeSubtotal(cart);
      const vat = subtotal * VAT_RATE;
      const total = subtotal + vat + DELIVERY_FEE;
      html += `</div>
    <hr style="margin:1rem 0;">
    <div style="display:flex;flex-direction:column;gap:.4rem;">
      <div style="display:flex;justify-content:space-between;"><span>Subtotal</span><strong>₱${subtotal.toFixed(2)}</strong></div>
      <div style="display:flex;justify-content:space-between;"><span>VAT (12%)</span><strong>₱${vat.toFixed(2)}</strong></div>
      <div style="display:flex;justify-content:space-between;"><span>Delivery Fee</span><strong>₱${DELIVERY_FEE.toFixed(2)}</strong></div>
      <div style="display:flex;justify-content:space-between;font-size:1.15em;font-weight:800;"><span>Total</span><strong>₱${total.toFixed(2)}</strong></div>
    </div>`;
      container.innerHTML = html;

      // wire up numeric inputs (because we generated DOM)
      for (const k of Object.keys(cart.items)) {
        const input = document.getElementById(`qty_${k}`);
        input.addEventListener('change', (ev) => {
          let v = Math.max(1, parseInt(ev.target.value || '1'));
          setQty(k, v);
        });
      }
    }

    /* Compute subtotal */
    function computeSubtotal(cart) {
      let sum = 0;
      if (!cart || !cart.items) return 0;
      for (const k of Object.keys(cart.items)) {
        const it = cart.items[k];
        sum += Number(it.price) * Number(it.qty);
      }
      return Number(sum.toFixed(2));
    }

    /* modify qty by delta */
    function changeQty(key, delta) {
      const cart = readCart();
      if (!cart || !cart.items || !cart.items[key]) return;
      cart.items[key].qty = Math.max(1, (Number(cart.items[key].qty) || 0) + delta);
      writeCart(cart);
      renderBasketLayer();
    }

    /* set absolute qty */
    function setQty(key, qty) {
      const cart = readCart();
      if (!cart || !cart.items || !cart.items[key]) return;
      cart.items[key].qty = Math.max(1, qty);
      writeCart(cart);
      renderBasketLayer();
    }

    /* remove item */
    function removeItem(key) {
      const cart = readCart();
      if (!cart || !cart.items || !cart.items[key]) return;
      const name = cart.items[key].name;
      delete cart.items[key];
      // if no items left, clear cart
      if (Object.keys(cart.items).length === 0) {
        clearCart();
      } else {
        writeCart(cart);
      }
      renderBasketLayer();
      flashToast(`${name} removed`);
    }

    /* When user clicks Place Order -> serialize cart into hidden input and submit */
    document.getElementById('placeOrderBtn').addEventListener('click', () => {
      const cart = readCart();
      if (!cart || !cart.items || Object.keys(cart.items).length === 0) {
        document.getElementById('basketErrors').textContent = "Your basket is empty.";
        return;
      }
      // put serialized cart in hidden field
      document.getElementById('cart_data').value = JSON.stringify(cart);
      // (optionally verify address non-empty)
      const adr = document.getElementById('destination_address').value.trim();
      if (!adr) {
        document.getElementById('basketErrors').textContent = "Please provide delivery address.";
        return;
      }
      // Submit (no ajax)
      document.getElementById('checkoutForm').submit();
    });

    /* Open basket when layer opens -> render */
    document.querySelectorAll('.layer-open').forEach(btn => {
      btn.addEventListener('click', (ev) => {
        // if opening basket layer, ensure render
        const target = btn.dataset.layerTarget;
        if (target === 'basket') {
          renderBasketLayer();
        }
      });
    });

    /* Update basket UI initially */
    updateBasketCountUI();
    renderBasketLayer();

    /* If page loaded with ?order=... we should clear cart and update UI (success) */
    <?php if ($placedOrder): ?>
      // Clear cart (we only clear in client after order placed)
      try { localStorage.removeItem(CART_KEY); } catch (e) { }
      updateBasketCountUI();
    <?php endif; ?>

    /* Utility: small toast (non-blocking) */
    function flashToast(msg) {
      try {
        const el = document.createElement('div');
        el.textContent = msg;
        el.style.position = 'fixed';
        el.style.right = '20px';
        el.style.bottom = '20px';
        el.style.background = 'rgba(16,24,40,0.95)';
        el.style.color = 'white';
        el.style.padding = '10px 14px';
        el.style.borderRadius = '10px';
        el.style.zIndex = 9999;
        document.body.appendChild(el);
        setTimeout(() => { el.style.transition = 'opacity .4s'; el.style.opacity = 0; setTimeout(() => el.remove(), 400); }, 1800);
      } catch (e) { }
    }

    /* escapeHtml small util */
    function escapeHtml(s) { return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); }

    /* Close layer buttons preserve usual behavior — layer.js may handle them,
       but ensure basket layer update when closed / opened */
    document.querySelectorAll('.layer-close').forEach(btn => {
      btn.addEventListener('click', () => {
        // re-render basket when closing (keeps UI consistent)
        setTimeout(() => {
          updateBasketCountUI();
          renderBasketLayer();
        }, 200);
      });
    });

    /* Add keyboard-friendly handling or other UI niceties here */
  </script>

</body>

</html>