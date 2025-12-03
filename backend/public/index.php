<?php
session_start();

$method = $_SERVER['REQUEST_METHOD'];
$path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$path = str_replace('/Jollikod_OMS/backend/public', '', $path);

$pdo = require_once __DIR__ . '/../database/connection.php';

require_once __DIR__ . '/../routes/api.php';
