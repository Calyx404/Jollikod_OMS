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

// ---------- HANDLE POST ACTIONS ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // ADD STAFF
    if ($_POST['action'] === 'add') {
        $stmt = $pdo->prepare("
            INSERT INTO staffs (name, email, phone, role)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([
            $_POST['name'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['role']
        ]);

        header("Location: staff.php");
        exit;
    }

    // EDIT STAFF
    if ($_POST['action'] === 'edit') {
        $stmt = $pdo->prepare("
            UPDATE staffs
            SET name = ?, email = ?, phone = ?, role = ?
            WHERE staff_id = ?
        ");

        $stmt->execute([
            $_POST['name'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['role'],
            $_POST['staff_id']
        ]);

        header("Location: staff.php");
        exit;
    }

    // DELETE STAFF (soft delete)
    if ($_POST['action'] === 'delete') {
        $stmt = $pdo->prepare("
            UPDATE staffs
            SET deleted_at = NOW()
            WHERE staff_id = ?
        ");

        $stmt->execute([$_POST['staff_id']]);

        header("Location: staff.php");
        exit;
    }
}

$stmt = $pdo->query("SELECT * FROM staffs WHERE deleted_at IS NULL ORDER BY created_at ASC");
$staffs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Our Management!</title>

    <link rel="stylesheet" href="../../assets/css/pages/branch/staff.css" />

    <script src="../../assets/js/components/layer.js" defer></script>
  </head>
  <body class="branch">
    <main class="page">
      <header class="header header-page">
        <div class="context">
          <h1>Staff</h1>
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
            <span class="btn-label">Customer</span>
            <i class="bx bxs-fork-spoon btn-icon"></i>
          </button>

          <button
            class="btn btn-primary layer-open"
            data-layer-target="add-staff"
          >
            <span class="btn-label">Add Staff</span>
            <i class="bx bxs-user-plus btn-icon"></i>
          </button>
        </div>
      </header>

      <main class="main-container main-scrollable">
        <main class="main">

          <div class="tab-container">
            <!-- radios -->
            <input type="radio" name="tab-group" id="tab-1" checked />

            <!-- tab bar (top) -->
            <div class="tab-bar">
              <label for="tab-1" class="tab">Management</label>
            </div>

            <!-- tab content -->
            <div class="tab-content" id="content-1">
              <div class="table-container" id="staff-management">
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
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Actions</th>
                      </tr>
                    </thead>

                    <tbody>
                      <?php if (count($staffs) === 0): ?>

                        <tr>
                          <td colspan="8">No records available.</td>
                        </tr>

                      <?php else: ?>
                        <?php foreach ($staffs as $entry): ?>
                          <tr>
                              <td><?= $entry['staff_id'] ?></td>
                              <td><?= htmlspecialchars($entry['name']) ?></td>
                              <td><?= htmlspecialchars($entry['email']) ?></td>
                              <td><?= htmlspecialchars($entry['phone']) ?></td>
                              <td><?= htmlspecialchars($entry['role']) ?></td>
                              <td><?= date('M d, Y h:i A', strtotime($entry['created_at'])) ?></td>
                              <td class="actions">
                                  <button 
                                      class="btn btn-primary layer-open"
                                      data-layer-target="edit-staff"
                                      data-staff-id="<?= $entry['staff_id'] ?>"
                                      data-staff-name="<?= htmlspecialchars($entry['name']) ?>"
                                      data-staff-email="<?= htmlspecialchars($entry['email']) ?>"
                                      data-staff-phone="<?= htmlspecialchars($entry['phone']) ?>"
                                      data-staff-role="<?= htmlspecialchars($entry['role']) ?>"
                                  >
                                      <span class="btn-label">Edit</span>
                                      <i class="bx bxs-edit btn-icon"></i>
                                  </button>

                                  <form method="post" style="display:inline;">
                                      <input type="hidden" name="action" value="delete" />
                                      <input type="hidden" name="staff_id" value="<?= $entry['staff_id'] ?>" />
                                      <button class="btn btn-danger" onclick="return confirm('Remove this staff?')">
                                          <span class="btn-label">Remove</span>
                                          <i class="bx bxs-trash btn-icon"></i>
                                      </button>
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

          </div>

        </main>
      </main>
    </main>

    <aside class="layer" id="add-staff">
      <header class="header header-page">
        <div class="actions left">
          <button class="btn btn-secondary layer-close" title="Close Panel">
            <i class="bx bxs-dock-right-arrow btn-icon"></i>
          </button>
        </div>
        <div class="context">
          <h1>Add Staff</h1>
        </div>
        </div>
      </header>

      <main class="main-container main-scrollable">
        <main class="main">
        <section class="form-container">  
        <form method="post" class="form panel-form">
            <input type="hidden" name="action" value="add">

            <div class="account-field">
              <label>Name</label>
              <div class="input-container">
                <input type="text" name="name" required placeholder="Full Name">
              </div>
            </div>

            <div class="account-field">
              <label>Email</label>
              <div class="input-container">
                <input type="email" name="email" required placeholder="Email Address">
              </div>
            </div>

            <div class="account-field">
              <label>Phone</label>
              <div class="input-container">
                <input type="text" name="phone" placeholder="Phone Number">
              </div>
            </div>

            <div class="account-field">
              <label>Role</label>
              <div class="input-container">
                <select name="role" required>
                  <option value="Cashier">Cashier</option>
                  <option value="Cook">Cook</option>
                  <option value="Crew">Crew</option>
                  <option value="Manager">Manager</option>
                </select>
              </div>
            </div>

            <div class="account-actions">
              <button type="button" class="btn btn-secondary layer-close">Cancel</button>
              <button class="btn btn-primary" type="submit">Add Staff</button>
            </div>
          </form>
          </section>
        </main>

      </main>
    </aside>

    <aside class="layer" id="edit-staff">
      <header class="header header-page">
        <div class="actions left">
          <button class="btn btn-secondary layer-close" title="Close Panel">
            <i class="bx bxs-dock-right-arrow btn-icon"></i>
          </button>
        </div>
        <div class="context">
          <h1>Staff Details</h1>
        </div>
      </header>

      <main class="main-container main-scrollable">
        <main class="main">
        <section class="form-container">  
        <form method="post" class="form panel-form" id="edit-staff-form">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="staff_id" id="edit-staff-id">

            <div class="account-field">
              <label>Name</label>
              <div class="input-container">
                <input type="text" name="name" id="edit-staff-name" required>
              </div>
            </div>

            <div class="account-field">
              <label>Email</label>
              <div class="input-container">
                <input type="email" name="email" id="edit-staff-email" required>
              </div>
            </div>

            <div class="account-field">
              <label>Phone</label>
              <div class="input-container">
                <input type="text" name="phone" id="edit-staff-phone">
              </div>
            </div>

            <div class="account-field">
              <label>Role</label>
              <div class="input-container">
                <select name="role" id="edit-staff-role" required>
                  <option value="Cashier">Cashier</option>
                  <option value="Cook">Cook</option>
                  <option value="Crew">Crew</option>
                  <option value="Manager">Manager</option>
                </select>
              </div>
            </div>

            <div class="account-actions">
              <button type="button" class="btn btn-secondary layer-close">Cancel</button>
              <button class="btn btn-primary" type="submit">Save</button>
            </div>
          </form>
          </section>
        </main>

      </main>
    </aside>

    <script>
    document.querySelectorAll("[data-layer-target='edit-staff']").forEach(btn => {
        btn.addEventListener("click", () => {
            document.getElementById("edit-staff-id").value = btn.dataset.staffId;
            document.getElementById("edit-staff-name").value = btn.dataset.staffName;
            document.getElementById("edit-staff-email").value = btn.dataset.staffEmail;
            document.getElementById("edit-staff-phone").value = btn.dataset.staffPhone;
            document.getElementById("edit-staff-role").value = btn.dataset.staffRole;
        });
    });
    </script>

  </body>
</html>
