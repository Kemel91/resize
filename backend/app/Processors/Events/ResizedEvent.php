<?php

namespace App\Processors\Events;

class ResizedEvent
{
    public function __construct(public string $url, public int $originalLength, public int $resizedLength)
    {
    }
}