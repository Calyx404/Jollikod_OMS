<?php
namespace Requests;
use Core\Validator;

/**
 * requests/LoginRequest.php
 *
 * Purpose:
 *  - Validate login payload (email + password).
 */
class LoginRequest {
    public static function validate($input) {
        $rules = [
            'email' => ['required','email'],
            'password' => ['required']
        ];
        return Validator::validate($input, $rules);
    }
}
