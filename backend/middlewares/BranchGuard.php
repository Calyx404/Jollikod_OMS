<?php
namespace Middleware;
use Core\Response;

/**
 * middleware/BranchGuard.php
 *
 * Purpose:
 *  - Protect endpoints intended only for branch main accounts.
 */

class BranchGuard {
    public static function handle($request) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['branch_id'])) {
            Response::json(['error' => 'Branches only'], 403);
        }
        return true;
    }
}
