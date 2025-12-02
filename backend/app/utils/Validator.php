<?php

class Validator
{
    public static function required(array $fields, array $input): array
    {
        $errors = [];

        foreach ($fields as $field) {
            if (!isset($input[$field]) || trim($input[$field]) === '') {
                $errors[$field] = "$field is required";
            }
        }

        return $errors;
    }
}
