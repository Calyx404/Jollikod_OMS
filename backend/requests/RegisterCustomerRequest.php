<?php
namespace Requests;
use Core\Validator;

/**
 * requests/RegisterCustomerRequest.php
 *
 * Purpose:
 *  - Validate customer registration payload.
 *
 * Flow:
 *  - Define validation rules and call Validator::validate()
 *  - Return sanitized input or error (Validator handles response on error)
 */
class RegisterCustomerRequest {
    public static function validate($input) {
        $rules = [
            'customer_name' => ['required'],
            'email' => ['required','email'],
            'password' => ['required','min:6'],
            'phone' => ['required'],
            'lot_number' => ['required'],
            'street' => ['required'],
            'city' => ['required'],
            'province' => ['required']
        ];
        return Validator::validate($input, $rules);
    }
}
