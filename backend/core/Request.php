<?php

class Request
{
    public function body(): array
    {
        $json = file_get_contents("php://input");
        $data = json_decode($json, true);

        return $data ?? $_POST ?? [];
    }

    public function query(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }
}
