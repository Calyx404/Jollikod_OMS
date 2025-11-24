<?php
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    
    if (isset($_SESSION['branch_id'])) {
        $id = $_SESSION['branch_id'];
        $stmt = $pdo->prepare("SELECT branch_id, owner_name, email, phone, lot_number, street, city, province, branch_verified FROM branches WHERE branch_id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['status'=>'ok','data'=>$row]);
        exit;
    }
    echo json_encode(['status'=>'error','msg'=>'not authenticated']);
    exit;
}

http_response_code(405);
echo json_encode(['status'=>'error','msg'=>'method not allowed']);
