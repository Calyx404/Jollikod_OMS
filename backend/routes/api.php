<?php
use Controllers\OrderController;
use Middleware\AuthMiddleware;


$router->get('/orders', [AuthMiddleware::class, 'handle'], [OrderController::class, 'index']);