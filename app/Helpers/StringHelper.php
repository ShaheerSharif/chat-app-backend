<?php

namespace App\Helpers;

class StringHelper
{
    public static function generateRandomString($length = 100)
    {
        return bin2hex(random_bytes(floor($length / 2)));
    }
}
