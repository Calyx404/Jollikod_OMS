<?php
namespace Middleware;
use Core\Response;

/**
 * middleware/StaffGuard.php
 *
 * Purpose:
 *  - Protect endpoints only staff can call.
 */

class StaffGuard {
    public static function handle($request) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['staff_id'])) {
            Response::json(['error' => 'Staff only'], 403);
        }
        return true;
    }
}
