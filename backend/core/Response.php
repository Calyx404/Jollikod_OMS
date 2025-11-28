<?php
namespace Core;

/**
 * core/Response.php
 *
 * Purpose:
 *  - Standardize JSON and HTML responses.
 *
 * Flow:
 *  - Send HTTP status code and content-type header.
 *  - Output payload (JSON-encoded or HTML).
 */

class Response {
    public static function json($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    public static function html($html, $status = 200) {
        http_response_code($status);
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
        exit;
    }
}
