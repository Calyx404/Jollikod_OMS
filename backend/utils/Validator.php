<?php
class Validator {
    public static function required($fields, $data) {
        $errors = [];
        foreach ($fields as $f) {
            if (!isset($data[$f]) || trim($data[$f]) === '') {
                $errors[$f] = 'required';
            }
        }
        return $errors;
    }
}
