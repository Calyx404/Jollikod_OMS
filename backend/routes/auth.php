<?php
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET' && ($action ?? '') === 'ping') {
    echo json_encode(['status'=>'ok','msg'=>'auth ping']);
    exit;
}

if ($method === 'POST') {
    $post = $_POST;
    $action = $post['action'] ?? ($_GET['action'] ?? null);

    require_once __DIR__ . '/../controllers/AuthController.php';

    if ($action === 'login') {
        AuthController::login($pdo, $post);
    }

    if ($action === 'register_customer') {
        AuthController::registerCustomer($pdo, $post);
    }

    if ($action === 'register_branch') {
        AuthController::registerBranch($pdo, $post);
    }

    // default
    echo json_encode(['status'=>'error','msg'=>'unknown auth action']);
    exit;
}

http_response_code(405);
echo json_encode(['status'=>'error','msg'=>'method not allowed']);
