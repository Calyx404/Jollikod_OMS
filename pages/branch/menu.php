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

$stmt = $pdo->query("SELECT * FROM menu_items WHERE deleted_at IS NULL ORDER BY created_at DESC");
$items = $stmt->fetchAll();

$stmt = $pdo->query("SELECT * FROM menu_inventories");
$inventories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Find your Cravings!</title>

    <link rel="stylesheet" href="../../assets/css/pages/branch/menu.css" />

    <script src="../../assets/js/components/layer.js" defer></script>
  </head>
  <body class="branch">
    <main class="page">
      <header class="header header-page">
        <div class="context">
          <h1>Menu</h1>
        </div>
        <div class="actions right">
          <button
            class="btn btn-primary layer-open"
            data-layer-target="restock"
          >
            <span class="btn-label">Restock</span>
            <i class="bx bxs-package btn-icon"></i>
          </button>  
        <button
            class="btn btn-primary layer-open"
            data-layer-target="create-item"
          >
            <span class="btn-label">Create Item</span>
            <i class="bx bxs-plus-big btn-icon"></i>
          </button>
        </div>
      </header>

      <main class="main-container main-scrollable">
        <main class="main">

            <div class="tab-container">
              <!-- radios -->
              <input type="radio" name="tab-group" id="tab-1" checked />
              <input type="radio" name="tab-group" id="tab-2" />

              <!-- tab bar (top) -->
              <div class="tab-bar">
                <label for="tab-1" class="tab">Items</label>
                <label for="tab-2" class="tab">Inventory</label>
              </div>

              <!-- tab content -->
              <div class="tab-content" id="content-1">
                <div class="table-container" id="menu-items">
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
                          <th>ID</th>
                          <th>Menu</th>
                          <th>Category</th>
                          <th>Name</th>
                          <th>Description</th>
                          <th>Price</th>
                          <th>Created</th>
                          <th>Updated</th>
                          <th>Actions</th>
                        </tr>
                      </thead>

                      <tbody>
                        <?php if (count($items) === 0): ?>

                          <tr>
                            <td colspan="8">No records available.</td>
                          </tr>

                        <?php else: ?>
                          <?php foreach ($items as $entry): ?>
                            <tr>
                                <td><?= $entry['menu_item_id'] ?></td>
                                <td><?= $entry['menu_id'] ?></td>
                                <td><?= $entry['menu_category_id'] ?></td>
                                <td><?= htmlspecialchars($entry['name']) ?></td>
                                <td><?= htmlspecialchars($entry['decription']) ?></td>
                                <td><?= $entry['price'] ?></td>
                                <td><?= $entry['created_at'] ?></td>
                                <td><?= $entry['updated_at'] ?></td>
                                <td>
                                <button class="btn btn-primary layer-open" data-layer-target="edit-item">
                                  <span class="btn-label">Edit</span>
                                  <i class="bx bxs-user-plus btn-icon"></i>
                                </button>
                              </td>
                            </tr>
                          <?php endforeach; ?>

                        <?php endif; ?>

                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <div class="tab-content" id="content-2">
                <div class="table-container" id="customer-feedback">
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
                          <th>ID</th>
                          <th>Menu</th>
                          <th>Item</th>
                          <th>Quantity</th>
                          <th>Status</th>
                          <th>Updated</th>
                        </tr>
                      </thead>

                      <tbody>
                        <?php if (count($inventories) === 0): ?>

                          <tr>
                            <td colspan="8">No records available.</td>
                          </tr>

                        <?php else: ?>
                          <?php foreach ($inventories as $entry): ?>
                            <tr>
                              <td><?= $entry['menu_inventory_id'] ?></td>
                              <td><?= $entry['menu_id'] ?></td>
                              <td><?= $entry['menu_item_id'] ?></td>
                              <td><?= $entry['stock_quantity'] ?></td>
                              <td><?= htmlspecialchars($entry['stock_status']) ?></td>
                              <td><?= $entry['updated_at'] ?></td>
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

    <aside class="layer" id="create-item">
      <header class="header header-page">
        <div class="actions left">
          <button class="btn btn-secondary layer-close" title="Close Panel">
            <i class="bx bxs-dock-right-arrow btn-icon"></i>
          </button>
        </div>
        <div class="context">
          <h1>New Item</h1>
        </div>
      </header>

      <main class="main-container main-scrollable">
        <main class="main">
          <div class="edit-panel" id="editPanel">
            <h2>Edit Menu Item</h2>
            <form id="editForm">
              <label for="editName">Item Name</label>
              <input type="text" id="editName" name="editName" required />

              <label for="editCategory">Category</label>
              <input
                type="text"
                id="editCategory"
                name="editCategory"
                required
              />

              <label for="editPrice">Price</label>
              <input
                type="number"
                step="0.01"
                id="editPrice"
                name="editPrice"
                required
              />

              <label for="editStock">Stock</label>
              <input type="number" id="editStock" name="editStock" required />

              <!-- Item Image -->
              <label>Item Image</label>
              <img
                src="../assets/default.png"
                alt="Item Image"
                class="image-preview"
                id="editImagePreview"
              />
              <div class="btn-group">
                <button type="button" id="removeImageBtn">Remove Image</button>
                <button type="button" id="uploadImageBtn">
                  Upload New Image
                </button>
              </div>

              <!-- Save / Cancel / Delete -->
              <div class="btn-group">
                <button type="submit">Save Changes</button>
                <button type="button" class="cancel-btn" id="cancelEditBtn">
                  Cancel
                </button>
                <button type="button" class="delete-btn" id="deleteBtn">
                  Delete
                </button>
              </div>
            </form>
          </div>
        </main>
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
      </header>

      <main class="main-container main-scrollable">
        <main class="main">Meow</main>
      </main>
    </aside>

    <aside class="layer" id="restock">
      <header class="header header-page">
        <div class="actions left">
          <button class="btn btn-secondary layer-close" title="Close Panel">
            <i class="bx bxs-dock-right-arrow btn-icon"></i>
          </button>
        </div>
        <div class="context">
          <h1>Restock</h1>
        </div>
      </header>

      <main class="main-container main-scrollable">
        <main class="main">Meow</main>
      </main>
    </aside>
  </body>
</html>
