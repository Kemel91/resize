<?php

namespace App\Processors;

use App\Processors\Dto\ResizedImage;
use App\Processors\Dto\ResizeInput;

interface ImageProcessorInterface
{
    public function process(ResizeInput $input): ResizedImage;
}