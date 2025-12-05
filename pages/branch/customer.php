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

$stmt = $pdo->query("SELECT * FROM customers WHERE deleted_at IS NULL ORDER BY created_at ASC");
$customers = $stmt->fetchAll();

$stmt = $pdo->query("SELECT * FROM feedbacks ORDER BY created_at DESC");
$feedbacks = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>How's our service?</title>

    <link rel="stylesheet" href="../../assets/css/pages/branch/customer.css" />

    <script src="../../assets/js/components/layer.js" defer></script>
  </head>
  <body class="branch">
    <main class="page">
      <header class="header header-page">
        <div class="context">
          <h1>Customer</h1>
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
            onclick="parent.navigate('./staff.php')"
            class="btn btn-primary subnav"
          >
            <span class="btn-label">Staff</span>
            <i class="bx bxs-chef-hat btn-icon"></i>
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
              <label for="tab-1" class="tab">Demographic</label>
              <label for="tab-2" class="tab">Feedback</label>
            </div>

            <!-- tab content -->
            <div class="tab-content" id="content-1">
              <div class="table-container" id="customer-demographic">
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
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Joined</th>
                      </tr>
                    </thead>

                    <tbody>
                      <?php if (count($customers) === 0): ?>

                        <tr>
                          <td colspan="8">No records available.</td>
                        </tr>

                      <?php else: ?>
                        <?php foreach ($customers as $entry): ?>
                          <tr>
                              <td><?= $entry['customer_id'] ?></td>
                              <td><?= htmlspecialchars($entry['name']) ?></td>
                              <td><?= htmlspecialchars($entry['email']) ?></td>
                              <td><?= htmlspecialchars($entry['phone']) ?></td>
                              <td><?= htmlspecialchars($entry['address']) ?></td>
                              <td><?= $entry['created_at'] ?></td>
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
                          <th>Branch</th>
                          <th>Customer</th>
                          <th>Rating</th>
                          <th>Message</th>
                          <th>Sent</th>
                      </tr>
                    </thead>

                    <tbody>
                      <?php if (count($feedbacks) === 0): ?>

                        <tr>
                          <td colspan="8">No records available.</td>
                        </tr>

                      <?php else: ?>
                        <?php foreach ($feedbacks as $entry): ?>
                          <tr>
                              <td><?= $entry['feedback_id'] ?></td>
                              <td><?= $entry['branch_id'] ?></td>
                              <td><?= $entry['customer_id'] ?></td>
                              <td><?= htmlspecialchars($entry['rating']) ?></td>
                              <td><?= htmlspecialchars($entry['message']) ?></td>
                              <td><?= $entry['created_at'] ?></td>
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
