<?php
class VALIDATION
{
    public static function validatePhoneNumber($phoneNumber)
    {
        $pattern = "/^[689]\d{7}$/";
        return preg_match($pattern, $phoneNumber) === 1;
    }

    public static function validateEmail($email)
    {
        $pattern = "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";
        return preg_match($pattern, $email);
    }
}
