<?php
namespace Services;

use Repositories\CustomerRepository;
use Repositories\BranchRepository;
use Core\Security;

/**
 * services/AuthService.php
 *
 * Purpose:
 *  - Business logic for register/login/logout (customer & branch).
 *
 * Flow:
 *  - register: check unique email -> hash password -> insert record
 *  - login: lookup -> verify password -> set session (customer_id or branch_id)
 *  - logout: unset session key
 */
class AuthService {
    public static function registerCustomer($data) {
        if (CustomerRepository::findByEmail($data['email'])) {
            return ['error' => 'Email already registered'];
        }
        $data['password'] = Security::hash($data['password']);
        $id = CustomerRepository::create($data);
        return ['id' => $id, 'email' => $data['email']];
    }

    public static function loginCustomer($email, $password) {
        $user = CustomerRepository::findByEmail($email);
        if (!$user || !Security::verify($password, $user['password'])) {
            return ['error' => 'Invalid credentials'];
        }
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['customer_id'] = $user['customer_id'];
        return ['success' => true, 'customer' => $user];
    }

    public static function logoutCustomer() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        unset($_SESSION['customer_id']);
        return ['success' => true];
    }

    public static function registerBranch($data) {
        if (BranchRepository::findByEmail($data['email'])) {
            return ['error' => 'Email already registered'];
        }
        $data['password'] = Security::hash($data['password']);
        $id = BranchRepository::create($data);
        return ['id' => $id, 'email' => $data['email']];
    }

    public static function loginBranch($email, $password) {
        $branch = BranchRepository::findByEmail($email);
        if (!$branch || !Security::verify($password, $branch['password'])) {
            return ['error' => 'Invalid credentials'];
        }
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['branch_id'] = $branch['branch_id'];
        return ['success' => true, 'branch' => $branch];
    }

    public static function logoutBranch() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        unset($_SESSION['branch_id']);
        return ['success' => true];
    }
}
