<?php

require __DIR__ . '/../core/bootstrap.php';

if (isset($_GET['api'])) {
    require __DIR__ . '/../core/router.php';
    exit;
}

echo json_encode(['status' => 'ok', 'msg' => 'Jollikod backend running']);
