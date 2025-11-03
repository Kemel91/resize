<?php
declare(strict_types=1);

namespace App\Processors;

class ResizeInput
{
    public function __construct(
        public string $url,
        public int $width,
        public ?int $height = null,
        public ?int $q = 75,
        public bool $cache = true,
    )
    {
    }
}