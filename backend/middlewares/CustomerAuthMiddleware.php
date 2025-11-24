<?php
class CustomerAuthMiddleware {
    public static function handle() {
        if (!isset($_SESSION['auth']) || $_SESSION['auth']['role'] !== 'customer') {
            echo json_encode(['status'=>'error','msg'=>'Unauthorized: customer only']);
            exit;
        }
    }
}
