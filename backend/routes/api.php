<?php

header('Content-Type: application/json');

require __DIR__ . '/../core/db.php';

$action = $_GET['action'] ?? null;

if ($action === 'branches') {
    $stmt = $pdo->query("SELECT branch_id, owner_name, city, province FROM branches LIMIT 50");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status'=>'ok','data'=>$rows]);
    exit;
}

echo json_encode(['status'=>'error','msg'=>'no action']);
