<?php
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {

    if (isset($_SESSION['customer_id'])) {
        $id = $_SESSION['customer_id'];
        $stmt = $pdo->prepare("SELECT customer_id, customer_name, email, phone, lot_number, street, city, province FROM customers WHERE customer_id = ?");
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
