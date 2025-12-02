<?php

class RoleMiddleware
{
    public static function requireRole(array $roles = [])
    {
        // branch & staff must be present
        AuthMiddleware::branch();
        AuthMiddleware::staff();

        // make sure staff role exists in session; otherwise fetch from DB
        if (!isset($_SESSION['staff_role'])) {
            $staffModel = new Staff();
            $staff = $staffModel->findById((int)$_SESSION['staff_id']);
            $_SESSION['staff_role'] = $staff['role'] ?? 'staff';
        }

        $currentRole = $_SESSION['staff_role'];

        if (empty($roles)) {
            return true;
        }

        if (!in_array($currentRole, $roles)) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden — insufficient role']);
            exit;
        }

        return true;
    }
}
