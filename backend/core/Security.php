<?php
namespace Core;

/**
 * core/Security.php
 *
 * Purpose:
 *  - Provide password hashing and verification helpers.
 *
 * Flow:
 *  - Uses PHP's password_hash and password_verify with BCRYPT.
 */

class Security {
    public static function hash($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public static function verify($password, $hash) {
        return password_verify($password, $hash);
    }
}
