<?php
namespace Requests;
use Core\Validator;


class BaseRequest {
public static function validate($input, $rules = []) {
return Validator::validate($input, $rules);
}
}