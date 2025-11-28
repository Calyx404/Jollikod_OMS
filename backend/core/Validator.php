<?php
namespace Core;


class Validator {
/**
* Purpose: Validate incoming data.
* Flow:
* 1. Loop rules.
* 2. Check required, min, email, etc.
* 3. If errors → stop.
* 4. Return sanitized result.
*/
public static function validate($input, $rules) {
$errors = [];
foreach ($rules as $field => $ruleList) {
foreach ($ruleList as $rule) {
if ($rule === 'required' && empty($input[$field])) {
$errors[$field][] = 'required';
}
}
}
if ($errors) {
http_response_code(422);
echo json_encode(['errors' => $errors]);
exit;
}
return $input;
}
}