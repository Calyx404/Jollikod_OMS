<?php
class BranchAuthMiddleware {
    public static function handle() {
        if (!isset($_SESSION['auth']) || $_SESSION['auth']['role'] !== 'branch') {
            echo json_encode(['status'=>'error','msg'=>'Unauthorized: branch only']);
            exit;
        }
    }
}
