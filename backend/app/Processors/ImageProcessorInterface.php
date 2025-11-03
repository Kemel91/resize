<?php

namespace App\Processors;

interface ImageProcessorInterface
{
    public function process(ResizeInput $input): ResizedImage;
}