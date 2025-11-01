<?php

namespace App\Processors;

interface ImageProcessorInterface
{
    public function process(string $url, int $width = 600, int $q = 85, bool $cache = false): ResizedImage;
}