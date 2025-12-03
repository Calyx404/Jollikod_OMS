<?php
require_once '../database/connection.php';
require_once '../utils/helpers.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    /* AUTH – CUSTOMER */
    case 'customer_register':
        $name     = trim($_POST['name']);
        $email    = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $phone    = $_POST['phone'] ?? null;
        $address  = $_POST['address'] ?? null;

        $stmt = $pdo->prepare("INSERT INTO customers (name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $phone, $address]);

        jsonResponse(['success' => true, 'message' => 'Customer registered']);
        break;

    case 'customer_login':
        $email    = trim($_POST['email']);
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT customer_id, name, password FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['customer_id'];
            $_SESSION['user_type'] = 'customer';
            $_SESSION['user_name'] = $user['name'];
            jsonResponse(['success' => true]);
        }
        jsonResponse(['success' => false, 'message' => 'Invalid credentials']);
        break;

    /* AUTH – BRANCH */
    case 'branch_register':
        $name     = trim($_POST['name']);
        $email    = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $phone    = $_POST['phone'] ?? null;
        $address  = $_POST['address'] ?? null;

        $stmt = $pdo->prepare("INSERT INTO branches (name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $phone, $address]);

        jsonResponse(['success' => true, 'message' => 'Branch registered']);
        break;

    case 'branch_login':
        $email    = trim($_POST['email']);
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT branch_id, name, password FROM branches WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['branch_id'];
            $_SESSION['user_type'] = 'branch';
            $_SESSION['user_name'] = $user['name'];
            jsonResponse(['success' => true]);
        }
        jsonResponse(['success' => false, 'message' => 'Invalid credentials']);
        break;

    case 'logout':
        session_destroy();
        jsonResponse(['success' => true]);
        break;


    /* PUBLIC */
    case 'getBranches':
        $stmt = $pdo->query("SELECT branch_id, name, address, phone FROM branches WHERE deleted_at IS NULL");
        jsonResponse(['success' => true, 'branches' => $stmt->fetchAll()]);
        break;

    case 'getMenuByBranch':
        $branch_id = (int)($_GET['branch_id'] ?? 0);
        if (!$branch_id) jsonResponse(['success' => false, 'message' => 'Invalid branch']);

        $pdo->prepare("INSERT IGNORE INTO menus (branch_id) VALUES (?)")->execute([$branch_id]);

        $stmt = $pdo->prepare("
            SELECT mi.*, COALESCE(inv.stock_quantity, 0) AS stock_quantity
            FROM menu_items mi
            LEFT JOIN menu_inventories inv USING (menu_item_id)
            WHERE mi.menu_id = (SELECT menu_id FROM menus WHERE branch_id = ?)
              AND mi.deleted_at IS NULL
              AND COALESCE(inv.stock_quantity, 0) > 0
            ORDER BY mi.menu_category_id, mi.name
        ");
        $stmt->execute([$branch_id]);
        jsonResponse(['success' => true, 'items' => $stmt->fetchAll()]);
        break;


    /* BRANCH ONLY */
    case 'getBranchMenu':
        requireBranch();
        $branch_id = $_SESSION['user_id'];

        $pdo->prepare("INSERT IGNORE INTO menus (branch_id) VALUES (?)")->execute([$branch_id]);

        $stmt = $pdo->prepare("
            SELECT mi.*, COALESCE(inv.stock_quantity, 0) AS stock_quantity,
                   COALESCE(inv.stock_status, 'out_of_stock') AS stock_status
            FROM menu_items mi
            LEFT JOIN menu_inventories inv ON mi.menu_item_id = inv.menu_item_id
            WHERE mi.menu_id = (SELECT menu_id FROM menus WHERE branch_id = ?)
              AND mi.deleted_at IS NULL
            ORDER BY mi.menu_category_id, mi.name
        ");
        $stmt->execute([$branch_id]);
        jsonResponse(['success' => true, 'items' => $stmt->fetchAll()]);
        break;

    case 'addMenuItem':
    case 'updateMenuItem':
        requireBranch();
        $branch_id = $_SESSION['user_id'];
        $menu_id   = $pdo->query("SELECT menu_id FROM menus WHERE branch_id = $branch_id")->fetchColumn();

        $name        = trim($_POST['name']);
        $description = $_POST['description'] ?? '';
        $price       = (float)$_POST['price'];
        $category_id = (int)$_POST['category_id'];
        $stock       = (int)($_POST['stock'] ?? 0);

        if ($action === 'addMenuItem') {
            $stmt = $pdo->prepare("INSERT INTO menu_items (menu_id, menu_category_id, name, description, price) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$menu_id, $category_id, $name, $description, $price]);
            $item_id = $pdo->lastInsertId();

            $pdo->prepare("INSERT INTO menu_inventories (menu_id, menu_item_id, stock_quantity) VALUES (?, ?, ?)")
                ->execute([$menu_id, $item_id, $stock]);
        } else {
            $item_id = (int)$_POST['id'];
            $pdo->prepare("UPDATE menu_items SET name=?, description=?, price=?, menu_category_id=? WHERE menu_item_id=? AND menu_id=?")
                ->execute([$name, $description, $price, $category_id, $item_id, $menu_id]);

            $pdo->prepare("INSERT INTO menu_inventories (menu_id, menu_item_id, stock_quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE stock_quantity = VALUES(stock_quantity)")
                ->execute([$menu_id, $item_id, $stock]);
        }
        jsonResponse(['success' => true]);
        break;

    case 'deleteMenuItem':
        requireBranch();
        $item_id   = (int)$_POST['id'];
        $branch_id = $_SESSION['user_id'];
        $menu_id   = $pdo->query("SELECT menu_id FROM menus WHERE branch_id = $branch_id")->fetchColumn();

        $pdo->prepare("UPDATE menu_items SET deleted_at = NOW() WHERE menu_item_id = ? AND menu_id = ?")
            ->execute([$item_id, $menu_id]);
        jsonResponse(['success' => true]);
        break;

    case 'getOrdersByStatus':
        requireBranch();
        $branch_id = $_SESSION['user_id'];
        $status    = $_GET['status'] ?? 'all';
        $date      = $_GET['date'] ?? date('Y-m-d');

        $sql = "SELECT o.*, c.name AS customer_name, p.total AS amount
                FROM orders o
                JOIN customers c ON o.customer_id = c.customer_id
                LEFT JOIN payments p ON o.order_id = p.order_id
                WHERE o.branch_id = ? AND DATE(o.created_at) = ?";
        $params = [$branch_id, $date];

        if ($status !== 'all') {
            $sql .= " AND o.status = ?";
            $params[] = $status;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        jsonResponse(['success' => true, 'orders' => $stmt->fetchAll()]);
        break;

    case 'updateOrderStatus':
        requireBranch();
        $order_id   = (int)$_POST['order_id'];
        $new_status = $_POST['status'];

        $pdo->prepare("UPDATE orders SET status = ? WHERE order_id = ? AND branch_id = ?")
            ->execute([$new_status, $order_id, $_SESSION['user_id']]);
        jsonResponse(['success' => true]);
        break;


    /* CUSTOMER ONLY */
    case 'placeOrder':
        requireCustomer();
        $customer_id       = $_SESSION['user_id'];
        $branch_id         = (int)$_POST['branch_id'];
        $items             = json_decode($_POST['items'], true);
        $destination_addr  = $_POST['destination_address'] ?? '';

        if (empty($items)) jsonResponse(['success' => false, 'message' => 'Cart is empty']);

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO orders (branch_id, customer_id, destination_address, status) VALUES (?, ?, ?, 'placed')");
            $stmt->execute([$branch_id, $customer_id, $destination_addr]);
            $order_id = $pdo->lastInsertId();

            $total = 0;
            foreach ($items as $item) {
                $qty   = (int)$item['qty'];
                $price = (float)$item['price'];
                $sub   = $qty * $price;
                $total += $sub;

                $pdo->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity, unit_price, total) VALUES (?, ?, ?, ?, ?)")
                    ->execute([$order_id, $item['id'], $qty, $price, $sub]);

                $pdo->prepare("UPDATE menu_inventories SET stock_quantity = stock_quantity - ? WHERE menu_item_id = ? AND stock_quantity >= ?")
                    ->execute([$qty, $item['id'], $qty]);
            }

            $pdo->prepare("INSERT INTO payments (order_id, subtotal, total, status) VALUES (?, ?, ?, 'paid')")
                ->execute([$order_id, $total, $total]);

            $pdo->commit();
            jsonResponse(['success' => true, 'order_id' => $order_id]);
        } catch (Exception $e) {
            $pdo->rollBack();
            jsonResponse(['success' => false, 'message' => 'Order failed']);
        }
        break;

    case 'getCustomerOrders':
        requireCustomer();
        $stmt = $pdo->prepare("
            SELECT o.*, b.name AS branch_name, p.total
            FROM orders o
            JOIN branches b ON o.branch_id = b.branch_id
            LEFT JOIN payments p ON o.order_id = p.order_id
            WHERE o.customer_id = ?
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        jsonResponse(['success' => true, 'orders' => $stmt->fetchAll()]);
        break;

    case 'confirmReceived':
        requireCustomer();
        $order_id = (int)$_POST['order_id'];
        $pdo->prepare("UPDATE orders SET status = 'received' WHERE order_id = ? AND customer_id = ?")
            ->execute([$order_id, $_SESSION['user_id']]);
        jsonResponse(['success' => true]);
        break;

    case 'submitFeedback':
        requireCustomer();
        $branch_id = (int)$_POST['branch_id'];
        $rating    = (int)$_POST['rating'];
        $message   = $_POST['message'] ?? '';

        $pdo->prepare("INSERT INTO feedbacks (branch_id, customer_id, rating, message) VALUES (?, ?, ?, ?)")
            ->execute([$branch_id, $_SESSION['user_id'], $rating, $message]);
        jsonResponse(['success' => true]);
        break;


    /* DEFAULT */
    default:
        jsonResponse(['success' => false, 'message' => 'Invalid action']);
}