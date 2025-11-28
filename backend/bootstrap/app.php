<?php
/**
 * bootstrap/app.php
 *
 * Purpose:
 *  - Bootstrap the application: load environment basics, start session,
 *    and prepare autoloader and basic config used by other files.
 *
 * Flow:
 *  - Require DB connection and core classes (Router/Request/Response)
 *  - Start session (session-based auth)
 *  - Return a config array for other scripts to use
 */

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../core/Request.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Router.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$config = [
    'env' => getenv('APP_ENV') ?: 'development',
    'debug' => getenv('APP_DEBUG') ?: true,
];

return $config;
