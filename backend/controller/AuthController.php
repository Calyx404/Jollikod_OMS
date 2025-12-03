<?php
// controller/AuthController.php

require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../utils/sanitizer.php';

class AuthController {

    public static function registerBranch($pdo) {
        Response::json(['message' => 'registerBranch called']);
    }

    public static function loginBranch($pdo) {
        Response::json(['message' => 'loginBranch called']);
    }

    public static function logoutBranch() {
        Response::json(['message' => 'logoutBranch called']);
    }

    public static function registerCustomer($pdo) {
        Response::json(['message' => 'registerCustomer called']);
    }

    public static function loginCustomer($pdo) {
        Response::json(['message' => 'loginCustomer called']);
    }

    public static function logoutCustomer() {
        Response::json(['message' => 'logoutCustomer called']);
    }
}
