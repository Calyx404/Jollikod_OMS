<?php
class CustomerAuth {
    public static function check() {
        if (!isset($_SESSION['customer_id'])) {
            echo json_encode(['status'=>'error','msg'=>'auth_required','redirect'=>'/frontend/routes/home/LoginCustomer.html']);
            exit;
        }
    }
}
