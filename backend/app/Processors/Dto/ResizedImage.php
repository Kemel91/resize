<?php

namespace App\Processors\Dto;

class ResizedImage
{
    public function __construct(
        public string $content,
        public string $mimeType = 'image/jpeg',
        public ?float $time = null,
    )
    {
    }
}