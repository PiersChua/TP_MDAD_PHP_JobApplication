<?php

/**
 * Helper functions for strings
 */

class StringUtils
{
    /**
     * Uppercases the first character of each word in the name
     */
    public static function capitalizeName(string $name): string
    {
        if (empty($name)) {
            return $name;
        }
        return ucwords(strtolower(trim($name)));
    }
    /**
     * Lowercases the email address
     */
    public static function lowercaseEmail(string $email): string
    {
        if (empty($email)) {
            return $email;
        }
        return strtolower(trim($email));
    }
}
