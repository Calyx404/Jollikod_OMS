<?php
// routes/auth.php
// Purpose: Register auth-related endpoints for customer and branch (register/login/logout)

use Controllers\AuthController;

// Customer endpoints
$router->post('/auth/register/customer', [[], Controllers\AuthController::class, 'registerCustomer']);
$router->post('/auth/login/customer', [[], Controllers\AuthController::class, 'loginCustomer']);
$router->post('/auth/logout/customer', [[Middleware\AuthMiddleware::class], Controllers\AuthController::class, 'logoutCustomer']);

// Branch endpoints
$router->post('/auth/register/branch', [[], Controllers\AuthController::class, 'registerBranch']);
$router->post('/auth/login/branch', [[], Controllers\AuthController::class, 'loginBranch']);
$router->post('/auth/logout/branch', [[Middleware\AuthMiddleware::class], Controllers\AuthController::class, 'logoutBranch']);
