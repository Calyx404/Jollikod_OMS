<?php

$action = $_GET['action'] ?? '';

if ($action === 'ping') {
    echo json_encode(['status' => 'ok', 'msg' => 'pong']);
    exit;
}

if ($action === 'branches') {
    // fetch sample branches
    $stmt = $pdo->query("SELECT branch_id, owner_name, city, province FROM branches LIMIT 50");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'ok', 'data' => $rows]);
    exit;
}

echo json_encode(['status' => 'error', 'msg' => 'unknown action']);


// header('Content-Type: application/json');
// require __DIR__ . '/../config/database.php';
// require __DIR__ . '/../controllers/AuthController.php';

// $auth = new AuthController($pdo);

// $action = $_GET['action'] ?? null;

// switch($action) {

//     case 'login':
//         $auth->login();
//         break;

//     case 'logout':
//         $auth->logout();
//         break;

//     case 'branches':
//         $stmt = $pdo->query("SELECT branch_id, owner_name, city, province FROM branches LIMIT 50");
//         $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
//         echo json_encode(['status'=>'ok','data'=>$rows]);
//         break;

//     default:
//         echo json_encode(['status'=>'error','msg'=>'invalid action']);
// }
