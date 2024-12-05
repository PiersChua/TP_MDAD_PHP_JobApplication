<?php
class Validation
{
    public static function validatePhoneNumber($phoneNumber): bool
    {
        $pattern = "/^[689]\d{7}$/";
        return preg_match($pattern, $phoneNumber) === 1;
    }

    public static function validateEmail($email): bool
    {
        $pattern = "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";
        return preg_match($pattern, $email) === 1;
    }

    public static function validateSchema($data, $schema): ?string
    {
        foreach ($schema as $field => $rules) {
            if ((!isset($data[$field]) || $data[$field] === "") && !empty($rules["required"])) {
                return $rules["message"];
            }
        }
        return null;
    }
}
