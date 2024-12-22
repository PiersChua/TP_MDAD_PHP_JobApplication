<?php

class StringUtils
{
    public static function capitalizeName(string $name): string
    {
        if (empty($name)) {
            return $name;
        }
        return ucwords(strtolower(trim($name)));
    }
    public static function lowercaseEmail(string $email): string
    {
        if (empty($email)) {
            return $email;
        }
        return strtolower(trim($email));
    }
}
