<?php

class Sanitizer
{
    public static function cleanString(string $value): string
    {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }
}
