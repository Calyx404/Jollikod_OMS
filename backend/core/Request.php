<?php
namespace Core;

/**
 * core/Request.php
 *
 * Purpose:
 *  - Encapsulate HTTP request data: method, uri, query, body, headers.
 *  - Provide helpers to access inputs (body/json/form).
 *
 * Flow:
 *  - Reads php://input to parse JSON payloads; falls back to $_POST.
 *  - Exposes capture() for router to use.
 */

class Request {
    public $method;
    public $uri;
    public $query;
    public $body;
    public $headers;

    public function __construct() {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->uri = strtok($_SERVER['REQUEST_URI'], '?');
        $this->query = $_GET ?: [];
        $this->headers = function_exists('getallheaders') ? getallheaders() : [];
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        $this->body = $json ?: $_POST;
    }

    public static function capture() {
        return new static();
    }

    public function input($key, $default = null) {
        if (isset($this->body[$key])) return $this->body[$key];
        if (isset($this->query[$key])) return $this->query[$key];
        return $default;
    }
}
