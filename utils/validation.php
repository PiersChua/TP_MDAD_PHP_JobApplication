<?php

/**
 * Validation class for different types of form validation
 */
class Validation
{
    private static int $minNameLength = 5;
    /**
     * Validates a name based on the 
     */
    public static function validateName($name): bool
    {
        // Check if the name is long enough
        if (strlen($name) < self::$minNameLength) {
            return false;
        }

        // Check if the name contains any numbers
        if (preg_match('/\d/', $name)) {
            return false;
        }
        return true;
    }
    /**
     * Validates a phone number based on Singapore's phone number format.
     * - Starts with 6, 8, or 9
     * - Contains 8 digits
     *
     * @param string $phoneNumber The phone number to validate.
     * @return bool True if the phone number is valid, false otherwise.
     */
    public static function validatePhoneNumber($phoneNumber): bool
    {
        $pattern = "/^[689]\d{7}$/";
        return preg_match($pattern, $phoneNumber) === 1;
    }
    /**
     * Validates an email address
     *
     * @param string $email The email to validate.
     * @return bool True if the email is valid, false otherwise.
     */
    public static function validateEmail($email): bool
    {
        $pattern = "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";
        return preg_match($pattern, $email) === 1;
    }
    /**
     * Validates superglobals against a schema.
     *
     * The schema defines the attributes that are required
     * If any required field is missing or empty, this method returns an error message.
     * 
     * Example schema:
     * [
     *     "fieldName" => [
     *         "required" => true,
     *         "message" => "Field is required"
     *     ]
     * ]
     *
     * @param array $data The input data to validate.
     * @param array $schema The schema defining validation rules for each field.
     * @return string|null An error message if validation fails, or null if all validations pass.
     */
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
