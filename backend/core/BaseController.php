<?php
class BaseController {
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    protected function json(Response $res, $data, int $status = 200) {
        // http_response_code($status);
        // header('Content-Type: application/json');
        // echo json_encode($data);
        // exit;

        return $res->json($data, $status);
    }
}

