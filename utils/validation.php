<?php

/**
 * Validation class for different types of form validation
 */
class Validation
{
    /**
     * Validates a name length
     */
    public static function validateUserName($name): bool
    {
        if (strlen($name) < 5) {
            return false;
        }
        if (strlen($name) > 100) {
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
     * Validates a job positon's length
     */
    public static function validateJobPosition($position)
    {
        if (strlen($position) < 10) {
            return false;
        }
        if (strlen($position) > 100) {
            return false;
        }
        return true;
    }
    /**
     * Validates a job responsibilities's length
     */
    public static function validateJobResponsibilities($responsibilities)
    {
        if (strlen($responsibilities) < 50) {
            return false;
        }
        if (strlen($responsibilities) > 1000) {
            return false;
        }
        return true;
    }

    /**
     * Validates a job description's length
     */
    public static function validateJobDescription($description)
    {
        if (strlen($description) < 50) {
            return false;
        }
        if (strlen($description) > 1000) {
            return false;
        }
        return true;
    }

    /**
     * Validates a job schedule's length
     */
    public static function validateJobSchedule($schedule)
    {
        if (strlen($schedule) < 5) {
            return false;
        }
        if (strlen($schedule) > 50) {
            return false;
        }
        return true;
    }

    /**
     * Validates a job location's length
     */
    public static function validateJobLocation($location)
    {
        if (strlen($location) < 2) {
            return false;
        }
        if (strlen($location) > 100) {
            return false;
        }
        return true;
    }
    /**
     * Validates a job organisation's length
     */
    public static function validateJobOrganisation($organisation)
    {
        if (strlen($organisation) < 2) {
            return false;
        }
        if (strlen($organisation) > 100) {
            return false;
        }
        return true;
    }

    /**
     * Validates an agency address's length
     */
    public static function validateAgencyAddress($address)
    {
        if (strlen($address) < 2) {
            return false;
        }
        if (strlen($address) > 100) {
            return false;
        }
        return true;
    }

    /**
     * Validates an agency name's length
     */
    public static function validateAgencyName($name): bool
    {
        if (strlen($name) < 5) {
            return false;
        }
        if (strlen($name) > 100) {
            return false;
        }

        // Check if the name contains any numbers
        if (preg_match('/\d/', $name)) {
            return false;
        }
        return true;
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
