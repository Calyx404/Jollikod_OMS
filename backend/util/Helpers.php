<?php
/**
 * utils/helpers.php
 *
 * Purpose:
 *  - Small helper functions used across the app.
 */

function input_get($request, $key, $default = null) {
    return $request->body[$key] ?? $request->query[$key] ?? $default;
}

function respond_success($data = []) {
    \Core\Response::json(array_merge(['success' => true], $data));
}

function respond_error($message, $code = 400) {
    \Core\Response::json(['error' => $message], $code);
}
