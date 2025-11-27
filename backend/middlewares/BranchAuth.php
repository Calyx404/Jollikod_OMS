<?php
class BranchAuth {
    public static function check() {
        if (!isset($_SESSION['branch_id'])) {
            echo json_encode(['status'=>'error','msg'=>'auth_required','redirect'=>'/frontend/routes/home/LoginBranch.html']);
            exit;
        }
    }
}
