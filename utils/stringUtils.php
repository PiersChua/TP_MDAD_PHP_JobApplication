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
}
