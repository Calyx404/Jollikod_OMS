<?php
namespace Middleware;
use Core\Response;

/**
 * middleware/CustomerGuard.php
 *
 * Purpose:
 *  - Protect endpoints intended only for customers.
 *
 * Flow:
 *  - Start session
 *  - Check $_SESSION['customer_id']
 *  - If missing -> 403
 */

class CustomerGuard {
    public static function handle($request) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['customer_id'])) {
            Response::json(['error' => 'Customers only'], 403);
        }
        return true;
    }
}
