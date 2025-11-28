<?php
namespace Middleware;
use Core\Response;

/**
 * middleware/AuthMiddleware.php
 *
 * Purpose:
 *  - Ensure *some* user session exists. This middleware is generic:
 *    it passes if customer, branch, or staff session key exists.
 *
 * Flow:
 *  - Start session if needed
 *  - If none of the session keys exist -> return 401 JSON
 */

class AuthMiddleware {
    public static function handle($request) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['customer_id']) && !isset($_SESSION['branch_id']) && !isset($_SESSION['staff_id'])) {
            Response::json(['error' => 'Unauthorized'], 401);
        }
        return true;
    }
}
