<?php
namespace Requests;
use Core\Validator;

/**
 * requests/RegisterBranchRequest.php
 *
 * Purpose:
 *  - Validate branch registration payload.
 */
class RegisterBranchRequest {
    public static function validate($input) {
        $rules = [
            'owner_name' => ['required'],
            'email' => ['required','email'],
            'franchise_number' => ['required'],
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
