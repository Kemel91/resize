<?php

namespace App\Helpers;

use function hash;

class HashHelper
{
    public static function hash(string $string): string
    {
        return hash('xxh128', $string);
    }
}