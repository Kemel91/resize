<?php

namespace App\Services;

class MimetypeService
{
    public function imageType(string $url)
    {
        $pathInfo = pathinfo($url, PATHINFO_EXTENSION);
        var_dump($pathInfo);
//        return match ($url) {};
    }
}