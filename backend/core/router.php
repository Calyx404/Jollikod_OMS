<?php

$resource = $_GET['resource'] ?? ($_POST['resource'] ?? null);
$action = $_GET['action'] ?? ($_POST['action'] ?? null);
$method = $_SERVER['REQUEST_METHOD'];

$routesMap = [
    'auth' => __DIR__ . '/../routes/auth.php',
    'customers' => __DIR__ . '/../routes/customers.php',
    'branches' => __DIR__ . '/../routes/branches.php',
    'logout' => __DIR__ . '/../routes/logout.php',
    'inventory_categories' => __DIR__ . '/../routes/inventory_categories.php',
    'inventory_items' => __DIR__ . '/../routes/inventory_items.php',
    'inventory_stocks' => __DIR__ . '/../routes/inventory_stocks.php',
    // add more as we implement them
];

if (!$resource && isset($_SERVER['PATH_INFO'])) {
    $parts = explode('/', trim($_SERVER['PATH_INFO'], '/'));
    $resource = $parts[0] ?? null;
    $action = $parts[1] ?? $action;
}

if ($resource && isset($routesMap[$resource])) {
    require $routesMap[$resource];
    exit;
}

http_response_code(400);
echo json_encode(['status'=>'error','msg'=>'invalid resource']);
