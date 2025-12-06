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

$errors = $errors ?? [];
$messages = $messages ?? [];

// ---------- HANDLE POST ACTIONS ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // CREATE ITEM
    if ($_POST['action'] === 'create') {
        $stmt = $pdo->prepare("
            INSERT INTO menu_items (menu_category_id, name, description, price, image_path)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_POST['menu_category_id'],
            $_POST['name'],
            $_POST['description'] ?? null,
            $_POST['price'],
            $_POST['image_path'] ?? null
        ]);

        $newId = $pdo->lastInsertId();

        // Optional: create inventory entry if initial stock provided
        if (isset($_POST['initial_stock']) && $_POST['initial_stock'] !== '') {
            $stmt = $pdo->prepare("
                INSERT INTO menu_inventories (menu_item_id, stock_quantity)
                VALUES (?, ?)
            ");
            $stmt->execute([$newId, (int)$_POST['initial_stock']]);
        }

        header("Location: menu.php?tab=1");
        exit;
    }


    // EDIT ITEM
    if ($_POST['action'] === 'edit') {
        $stmt = $pdo->prepare("
            UPDATE menu_items
            SET menu_category_id = ?, name = ?, description = ?, price = ?, image_path = ?
            WHERE menu_item_id = ?
        ");
        $stmt->execute([
            $_POST['menu_category_id'],
            $_POST['name'],
            $_POST['description'] ?? null,
            $_POST['price'],
            $_POST['image_path'] ?? null,
            $_POST['menu_item_id']
        ]);

        header("Location: menu.php?edit=" . $_POST['menu_item_id']);
        exit;
    }


    // DELETE ITEM (soft delete)
    if ($_POST['action'] === 'delete') {
        $stmt = $pdo->prepare("UPDATE menu_items SET deleted_at = NOW() WHERE menu_item_id = ?");
        $stmt->execute([$_POST['menu_item_id']]);

        header("Location: menu.php?tab=1");
        exit;
    }


    // RESTOCK
    if ($_POST['action'] === 'restock') {
        $id = (int)$_POST['menu_item_id'];
        $amount = (int)$_POST['amount'];

        // Check if inventory exists
        $check = $pdo->prepare("SELECT menu_inventory_id, stock_quantity FROM menu_inventories WHERE menu_item_id = ?");
        $check->execute([$id]);
        $inv = $check->fetch(PDO::FETCH_ASSOC);

        if ($inv) {
            // Update existing record
            $stmt = $pdo->prepare("
                UPDATE menu_inventories
                SET stock_quantity = stock_quantity + ?, updated_at = NOW()
                WHERE menu_item_id = ?
            ");
            $stmt->execute([$amount, $id]);
        } else {
            // Create new inventory record
            $stmt = $pdo->prepare("
                INSERT INTO menu_inventories (menu_item_id, stock_quantity)
                VALUES (?, ?)
            ");
            $stmt->execute([$id, $amount]);
        }

        header("Location: menu.php?tab=2");
        exit;
    }

}

// ---------- FETCH DATA FOR PAGE ----------
$stmt = $pdo->prepare("SELECT * FROM menu_items WHERE deleted_at IS NULL ORDER BY created_at DESC");
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT inv.*, mi.name AS item_name FROM menu_inventories inv LEFT JOIN menu_items mi ON inv.menu_item_id = mi.menu_item_id ORDER BY inv.updated_at DESC, inv.menu_inventory_id");
$stmt->execute();
$inventories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// fetch categories as associative array [id => name]
$stmt = $pdo->query("SELECT menu_category_id, name FROM menu_categories");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$categories = [];
foreach ($rows as $r) $categories[(int)$r['menu_category_id']] = $r['name'];

// load editing/viewing/restock if present (same as before)
$editing = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE menu_item_id = ? LIMIT 1");
    $stmt->execute([$id]);
    $editing = $stmt->fetch(PDO::FETCH_ASSOC);
}

$viewing = null;
$viewing_inv = null;
if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    $id = (int)$_GET['view'];
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE menu_item_id = ? LIMIT 1");
    $stmt->execute([$id]);
    $viewing = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT * FROM menu_inventories WHERE menu_item_id = ? LIMIT 1");
    $stmt->execute([$id]);
    $viewing_inv = $stmt->fetch(PDO::FETCH_ASSOC);
}

$restock_open = isset($_GET['restock']);
$tabNum = isset($_GET['tab']) && in_array((int)$_GET['tab'], [1,2]) ? (int)$_GET['tab'] : 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Menu Management</title>

  <link rel="stylesheet" href="../../assets/css/pages/branch/menu.css" />
  <script src="../../assets/js/components/layer.js" defer></script>

</head>
<body class="branch">
  <main class="page">
    <header class="header header-page">
      <div class="context"><h1>Menu</h1></div>
      <div class="actions right">
        <button class="btn btn-primary layer-open" data-layer-target="restock"><span class="btn-label">Restock</span><i class="bx bxs-package btn-icon"></i></button>
        <button class="btn btn-primary layer-open" data-layer-target="create-item"><span class="btn-label">Create Item</span><i class="bx bxs-plus-big btn-icon"></i></button>
      </div>
    </header>

    <main class="main-container main-scrollable">
      <main class="main">
        <div class="tab-container">
          <input type="radio" name="tab-group" id="tab-1" <?= $tabNum===1 ? 'checked' : '' ?> />
          <input type="radio" name="tab-group" id="tab-2" <?= $tabNum===2 ? 'checked' : '' ?> />

          <div class="tab-bar">
            <label for="tab-1" class="tab">Items</label>
            <label for="tab-2" class="tab">Inventory</label>
          </div>

          <!-- ITEMS TAB -->
          <div class="tab-content" id="content-1">
            <div class="table-container" id="menu-items">
              <div class="table-header">
                <form class="table-filter">
                  <div class="field"><label>Date</label><input type="date" /></div>
                  <div class="field"><label>Sort By</label>
                    <select><option value="date">Date</option><option value="name">Name</option></select>
                  </div>
                  <button class="btn btn-primary"><span class="btn-label">Apply</span></button>
                </form>
              </div>

              <div class="table-content">
                <table>
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Category</th>
                      <th>Name</th>
                      <th>Description</th>
                      <th>Price</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (count($items) === 0): ?>
                      <tr><td colspan="6">No records available.</td></tr>
                    <?php else: ?>
                      <?php foreach ($items as $entry): ?>
                        <tr>
                          <td><?= $entry['menu_item_id'] ?></td>
                          <td><?= htmlspecialchars($categories[(int)$entry['menu_category_id']] ?? 'Unknown') ?></td>
                          <td><?= htmlspecialchars($entry['name']) ?></td>
                          <td class="ellipsis"><?= htmlspecialchars($entry['description'] ?? '') ?></td>
                          <td>₱<?= number_format($entry['price'],2) ?></td>
                          <td class="actions">
                            <a class="btn btn-secondary" href="menu.php?view=<?= $entry['menu_item_id'] ?>"><span class="btn-label">View</span><i class='bx  bxs-dots-horizontal-rounded btn-icon'></i> </a>
                            <a class="btn btn-primary" href="menu.php?edit=<?= $entry['menu_item_id'] ?>"><span class="btn-label">Edit</span><i class="bx bxs-edit btn-icon"></i></a>

                            <form style="display:inline" method="post" onsubmit="return confirm('Delete this item?');">
                              <input type="hidden" name="action" value="delete" />
                              <input type="hidden" name="menu_item_id" value="<?= $entry['menu_item_id'] ?>" />
                              <button class="btn btn-danger" type="submit"><span class="btn-label">Remove</span><i class="bx bxs-trash btn-icon"></i></button>
                            </form>
                            </form>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- INVENTORIES TAB -->
          <div class="tab-content" id="content-2">
            <div class="table-container" id="menu-inventories">
              <div class="table-header">
                <form class="table-filter">
                  <div class="field"><label>Date</label><input type="date" /></div>
                  <div class="field"><label>Sort By</label><select><option value="date">Date</option><option value="name">Name</option></select></div>
                  <button class="btn btn-primary"><span class="btn-label">Apply</span></button>
                </form>
              </div>

              <div class="table-content">
                <table>
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Item</th>
                      <th>Quantity</th>
                      <th>Status</th>
                      <th>Updated</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (count($inventories) === 0): ?>
                      <tr><td colspan="5">No records available.</td></tr>
                    <?php else: ?>
                      <?php foreach ($inventories as $entry): 
                        $qty = (int)$entry['stock_quantity'];
                        // map DB statuses to friendly statuses (but prefer computed)
                        if ($qty <= 0) { $friendly = 'Unavailable'; $cls = 'status-unavailable'; }
                        elseif ($qty <= 5) { $friendly = 'Low Stock'; $cls = 'status-low'; }
                        else { $friendly = 'Available'; $cls = 'status-available'; }
                      ?>
                        <tr>
                          <td><?= $entry['menu_inventory_id'] ?></td>
                          <td><?= htmlspecialchars($entry['item_name'] ?? $entry['menu_item_id']) ?></td>
                          <td><?= $qty ?></td>
                          <td><span class="status-badge <?= $cls ?>"><?= $friendly ?></span></td>
                          <td><?= date('M d, Y h:i A', strtotime($entry['updated_at'])) ?></td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

        </div>
      </main>
    </main>
  </main>

  <!-- CREATE ITEM LAYER (uses your .form / .input-container pattern) -->
  <aside class="layer <?= isset($_GET['create']) ? 'layer-active' : '' ?>" id="create-item">
    <header class="header header-page">
      <div class="actions left"><button class="btn btn-secondary layer-close"><i class="bx bxs-dock-right-arrow btn-icon"></i></button></div>
      <div class="context"><h1>New Item</h1></div>
    </header>

    <main class="main-container main-scrollable">
      <main class="main">
        <?php if ($errors): ?><div class="inventory-warning"><?= implode('<br>', $errors) ?></div><?php endif; ?>

          <section class="content-container">
          <form method="post" class="form panel-form">
          <input type="hidden" name="action" value="create" />

          <div class="account-field form-row">
            <div class="field">
              <label>Category</label>
              <div class="input-container">
                <select name="menu_category_id">
                  <?php foreach($categories as $id => $n): ?>
                    <option value="<?= $id ?>"><?= htmlspecialchars($n) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="field">
              <label>Price</label>
              <div class="input-container">
                <input type="number" name="price" step="0.01" required />
              </div>
            </div>
          </div>

          <div class="account-field">
            <label>Item Name</label>
            <div class="input-container">
              <input type="text" name="name" required />
            </div>
          </div>

          <div class="account-field">
            <label>Description</label>
            <div class="input-container">
              <textarea rows="5" name="description" placeholder="Describe the item..."></textarea>
            </div>
          </div>

          <div class="account-field form-row">
            <div class="field">
              <label>Image Path (optional)</label>
              <div class="input-container">
                <input type="text" name="image_path" placeholder="/upload/your-image.jpg" />
              </div>
            </div>

            <div class="field">
              <label>Initial Stock (optional)</label>
              <div class="input-container">
                <input type="number" name="initial_stock" min="0" />
              </div>
            </div>
          </div>

          <div class="account-actions">
            <button type="button" class="btn btn-secondary layer-close">Cancel</button>
            <button class="btn btn-primary" type="submit">Create Item</button>
          </div>
        </form>
        </section>
      </main>
    </main>
  </aside>

  <!-- EDIT ITEM LAYER -->
  <aside class="layer <?= $editing ? 'layer-active' : '' ?>" id="edit-item">
    <header class="header header-page">
      <div class="actions left"><button class="btn btn-secondary layer-close"><i class="bx bxs-dock-right-arrow btn-icon"></i></button></div>
      <div class="context"><h1>Edit Item</h1></div>
    </header>

    <main class="main-container main-scrollable">
      <main class="main">
        <?php if ($editing): ?>
  <section class="content-container">        
          <form method="post" class="form panel-form">
            <input type="hidden" name="action" value="edit" />
            <input type="hidden" name="menu_item_id" value="<?= $editing['menu_item_id'] ?>" />

            <div class="account-field form-row">
              <div class="field">
                <label>Category</label>
                <div class="input-container">
                  <select name="menu_category_id">
                    <?php foreach($categories as $id => $n): ?>
                      <option value="<?= $id ?>" <?= $id == $editing['menu_category_id'] ? 'selected' : '' ?>><?= htmlspecialchars($n) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <div class="field">
                <label>Price</label>
                <div class="input-container">
                  <input type="number" step="0.01" name="price" value="<?= $editing['price'] ?>" required />
                </div>
              </div>
            </div>

            <div class="account-field">
              <label>Item Name</label>
              <div class="input-container">
                <input type="text" name="name" value="<?= htmlspecialchars($editing['name']) ?>" required />
              </div>
            </div>

            <div class="account-field">
              <label>Description</label>
              <div class="input-container">
                <textarea rows="5" name="description"><?= htmlspecialchars($editing['description']) ?></textarea>
              </div>
            </div>

            <div class="account-field">
              <label>Image Path</label>
              <div class="input-container">
                <input type="text" name="image_path" value="<?= htmlspecialchars($editing['image_path']) ?>" />
              </div>
            </div>

            <div class="account-actions">
              <button type="button" class="btn btn-secondary layer-close">Cancel</button>
              <button class="btn btn-primary" type="submit">Save</button>
            </div>
          </form>
        </section>
        <?php else: ?>
          <p>No item selected for editing.</p>
        <?php endif; ?>
      </main>
    </main>
  </aside>

  <!-- VIEW ITEM LAYER -->
  <aside class="layer <?= $viewing ? 'layer-active' : '' ?>" id="view-item">
    <header class="header header-page">
      <div class="actions left"><button class="btn btn-secondary layer-close"><i class="bx bxs-dock-right-arrow btn-icon"></i></button></div>
      <div class="context"><h1>View Item</h1></div>
    </header>

    <main class="main-container main-scrollable">
      <main class="main">
        <?php if ($viewing): ?>
          <section class="content-container receipt">
            <?php if (!empty($viewing_inv)): 
              $qty = (int)$viewing_inv['stock_quantity'];
              if ($qty <= 0) { $friendly = 'Unavailable'; $cls = 'status-unavailable'; }
              elseif ($qty <= 5) { $friendly = 'Low Stock'; $cls = 'status-low'; }
              else { $friendly = 'Available'; $cls = 'status-available'; }
            ?>

            <div class="receipt-section highlight">
              <p class="status-badge <?= $cls ?>"><?= $friendly ?></p>
              <h4><?= htmlspecialchars($viewing['name']) ?></h4>
              <p><?= $viewing_inv['updated_at'] ?></p>
            </div>
          
            <div class="receipt-section">
              <div class="section-title">
                <h5>Details</h5>
                <div class="title-line"></div>
              </div>

              <div class="section-content">
                <div class="item-row">
                  <span class="left">Price</span>
                  <span class="right">₱<?= number_format($viewing['price'],2) ?></span>
                </div>

                <div class="item-row">
                  <span class="left">Stocks Left</span>
                  <span class="right"><?= $qty ?></span>
                </div>
              </div>
            </div>

            <div class="receipt-section">
              <div class="section-title">
                <h5>Description</h5>
                <div class="title-line"></div>
              </div>

              <div class="section-content">
                <p><?= htmlspecialchars($viewing['description']) ?></p>
              </div>
            </div>

          <?php else: ?>
            <p>No inventory record found for this item.</p>
          <?php endif; ?>

          <div class="actions-row">
            <a class="btn btn-secondary" href="menu.php?edit=<?= $viewing['menu_item_id'] ?>">Edit</a>
            <a class="btn btn-primary" href="menu.php?restock=1&item=<?= $viewing['menu_item_id'] ?>">Restock</a>
          </div>
          </section>
        <?php else: ?>
          <p>No item selected for viewing.</p>
        <?php endif; ?>
      </main>
    </main>
  </aside>

  <!-- RESTOCK LAYER -->
  <aside class="layer <?= $restock_open ? 'layer-active' : '' ?>" id="restock">
    <header class="header header-page">
      <div class="actions left"><button class="btn btn-secondary layer-close"><i class="bx bxs-dock-right-arrow btn-icon"></i></button></div>
      <div class="context"><h1>Restock Item</h1></div>
    </header>

    <main class="main-container main-scrollable">
      <main class="main">
        <?php if ($errors): ?><div class="inventory-warning"><?= implode('<br>', $errors) ?></div><?php endif; ?>

          <section class="content-container">
          <form method="post" class="form panel-form">
          <input type="hidden" name="action" value="restock" />
          <div class="account-field">
            <label>Item ID</label>
            <div class="input-container">
              <input type="number" name="menu_item_id" min="1" placeholder="Use Items tab to find IDs." value="<?= isset($_GET['item']) ? (int)$_GET['item'] : '' ?>" required />
            </div>
          </div>

          <div class="account-field">
            <label>Quantity to Add</label>
            <div class="input-container">
              <input type="number" name="amount" min="1" value="1" required />
            </div>
          </div>

          <div class="account-actions">
            <button type="button" class="btn btn-secondary layer-close">Cancel</button>
            <button class="btn btn-primary" type="submit">Add Stock</button>
          </div>
        </form>
        </section>
      </main>
    </main>
  </aside>

</body>
</html>
