<?php

require __DIR__ . '/../core/bootstrap.php';

if (isset($_GET['api'])) {
    require __DIR__ . '/../routes/api.php';
    exit;
}
echo json_encode(['status'=>'ok','msg'=>'Backend running']);
