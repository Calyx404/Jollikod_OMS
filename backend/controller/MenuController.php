<?php
require_once '../database/connection.php';
require_once '../utils/session.php';

class MenuController {

    // Get menu for the logged-in branch
    public static function getMenu() {
        checkAuth('branch');
        global $pdo;
        $branchId = $_SESSION['user_id'];

        // Fetch menu
        $stmt = $pdo->prepare("SELECT menu_id FROM menus WHERE branch_id = ?");
        $stmt->execute([$branchId]);
        $menu = $stmt->fetch();
        if (!$menu) {
            echo json_encode(['message' => 'Menu not found']);
            return;
        }

        $menuId = $menu['menu_id'];

        // Fetch categories and items
        $stmt = $pdo->prepare("
            SELECT mc.menu_category_id, mc.name AS category_name, mi.menu_item_id, mi.name AS item_name, mi.description, mi.price, mi.image_path,
                   mi.menu_category_id, mi.menu_id, mi.created_at,
                   mi.menu_item_id, mi.name,
                   mi.price, mi.description,
                   mi.image_path,
                   COALESCE(mi.stock_quantity, 0) as stock_quantity,
                   CASE 
                       WHEN mi.stock_quantity = 0 THEN 'out_of_stock'
                       WHEN mi.stock_quantity <= 5 THEN 'low_stock'
                       ELSE 'in_stock'
                   END AS availability
            FROM menu_categories mc
            LEFT JOIN menu_items mi ON mi.menu_category_id = mc.menu_category_id
            WHERE mi.menu_id = ?
        ");
        $stmt->execute([$menuId]);
        $rows = $stmt->fetchAll();

        // Organize data by category
        $categories = [];
        foreach ($rows as $row) {
            $catId = $row['menu_category_id'];
            if (!isset($categories[$catId])) {
                $categories[$catId] = [
                    'id' => $catId,
                    'category_name' => $row['category_name'],
                    'items' => []
                ];
            }
            if ($row['menu_item_id']) {
                $categories[$catId]['items'][] = [
                    'id' => $row['menu_item_id'],
                    'item_name' => $row['item_name'],
                    'description' => $row['description'],
                    'price' => $row['price'],
                    'image_url' => $row['image_path'],
                    'stock_quantity' => $row['stock_quantity'],
                    'availability' => $row['availability']
                ];
            }
        }

        echo json_encode(['menu_id' => $menuId, 'categories' => array_values($categories)]);
    }

    // Create menu (if branch has none)
    public static function createMenu() {
        checkAuth('branch');
        global $pdo;
        $branchId = $_SESSION['user_id'];

        // Check if menu exists
        $stmt = $pdo->prepare("SELECT * FROM menus WHERE branch_id = ?");
        $stmt->execute([$branchId]);
        if ($stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['error' => 'Menu already exists']);
            return;
        }

        $stmt = $pdo->prepare("INSERT INTO menus (branch_id) VALUES (?)");
        $stmt->execute([$branchId]);
        $menuId = $pdo->lastInsertId();
        echo json_encode(['message' => 'Menu created successfully', 'menu_id' => $menuId]);
    }

    // Add category
    public static function addCategory() {
        checkAuth('branch');
        global $pdo;
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['category_name'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Category name required']);
            return;
        }

        $stmt = $pdo->prepare("INSERT INTO menu_categories (name) VALUES (?)");
        $stmt->execute([$data['category_name']]);
        $catId = $pdo->lastInsertId();
        echo json_encode(['message' => 'Category added', 'category_id' => $catId]);
    }

    // Add menu item
    public static function addMenuItem() {
        checkAuth('branch');
        global $pdo;
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['menu_id'], $data['category_id'], $data['item_name'], $data['price'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        $stmt = $pdo->prepare("
            INSERT INTO menu_items (menu_id, menu_category_id, name, description, price, image_path) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['menu_id'],
            $data['category_id'],
            $data['item_name'],
            $data['description'] ?? null,
            $data['price'],
            $data['image_path'] ?? null
        ]);
        $itemId = $pdo->lastInsertId();

        // Initialize stock
        $stmt = $pdo->prepare("INSERT INTO menu_inventories (menu_id, menu_item_id, stock_quantity) VALUES (?, ?, ?)");
        $stmt->execute([$data['menu_id'], $itemId, $data['stock_quantity'] ?? 0]);

        echo json_encode(['message' => 'Menu item added', 'item_id' => $itemId]);
    }

    // Update menu item
    public static function updateMenuItem() {
        checkAuth('branch');
        global $pdo;
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['item_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Item ID required']);
            return;
        }

        $stmt = $pdo->prepare("UPDATE menu_items SET name = ?, description = ?, price = ?, image_path = ? WHERE menu_item_id = ?");
        $stmt->execute([
            $data['item_name'] ?? null,
            $data['description'] ?? null,
            $data['price'] ?? null,
            $data['image_path'] ?? null,
            $data['item_id']
        ]);

        // Update stock if provided
        if (isset($data['stock_quantity'])) {
            $stmt = $pdo->prepare("UPDATE menu_inventories SET stock_quantity = ? WHERE menu_item_id = ?");
            $stmt->execute([$data['stock_quantity'], $data['item_id']]);
        }

        echo json_encode(['message' => 'Menu item updated']);
    }

    // Soft delete menu item
    public static function deleteMenuItem() {
        checkAuth('branch');
        global $pdo;
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['item_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Item ID required']);
            return;
        }

        $stmt = $pdo->prepare("DELETE FROM menu_items WHERE menu_item_id = ?");
        $stmt->execute([$data['item_id']]);

        echo json_encode(['message' => 'Menu item deleted']);
    }
}
