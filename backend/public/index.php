<?php
header('Content-Type: application/json');

// Start session
session_start();

// Load helpers
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../utils/helpers.php';
require_once __DIR__ . '/../utils/sanitizer.php';

// Load database
require_once __DIR__ . '/../database/connection.php';

// Load routes
require_once __DIR__ . '/../routes/api.php';
