<?php

class AuthMiddleware
{
    // Checks if a customer is logged in
    public static function customer()
    {
        if (!isset($_SESSION['customer_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Customer not logged in']);
            exit;
        }
    }

    // Checks if a branch is logged in
    public static function branch()
    {
        if (!isset($_SESSION['branch_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Branch not logged in']);
            exit;
        }
    }

    // Checks if a staff is logged in
    public static function staff()
    {
        if (!isset($_SESSION['staff_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Staff not logged in']);
            exit;
        }
    }
}
