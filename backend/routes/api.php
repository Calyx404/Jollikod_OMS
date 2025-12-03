<?php

require_once __DIR__ . '/../controller/AuthController.php';
require_once __DIR__ . '/../controller/BranchController.php';
require_once __DIR__ . '/../controller/MenuController.php';
// require_once __DIR__ . '/../controller/InventoryController.php';
// require_once __DIR__ . '/../controller/OrderController.php';
// require_once __DIR__ . '/../controller/PaymentController.php';
// require_once __DIR__ . '/../controller/FeedbackController.php';
// require_once __DIR__ . '/../controller/StaffController.php';
// require_once __DIR__ . '/../controller/AnalyticsController.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/Jollikod_OMS/public', '', $path);

// Simple routing
switch(true) {

    // AUTH
    case $method === 'POST' && $path === '/api/auth/register-branch':
        AuthController::registerBranch($pdo);
        break;

    case $method === 'POST' && $path === '/api/auth/login-branch':
        AuthController::loginBranch($pdo);
        break;

    case $method === 'POST' && $path === '/api/auth/logout-branch':
        AuthController::logoutBranch();
        break;

    case $method === 'POST' && $path === '/api/auth/register-customer':
        AuthController::registerCustomer($pdo);
        break;

    case $method === 'POST' && $path === '/api/auth/login-customer':
        AuthController::loginCustomer($pdo);
        break;

    case $method === 'POST' && $path === '/api/auth/logout-customer':
        AuthController::logoutCustomer();
        break;

    // Add additional endpoints here following the same pattern

    default:
        Response::json(['error' => 'Endpoint not found'], 404);
        break;
}