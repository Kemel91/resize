<?php
declare(strict_types=1);

namespace App\Processors;

use App\Services\Download\ImageDownloadService;
use Intervention\Image\Drivers\Vips\Driver as VipsDriver;
use Intervention\Image\ImageManager;

class ImageInterventionProcessor implements ImageProcessorInterface
{
    private ImageManager $imageManager;

    public function __construct(
        private readonly ImageDownloadService $imageDownloadService,
    ) {
        $this->imageManager = ImageManager::withDriver(VipsDriver::class);
    }

    public function process(ResizeInput $input): ResizedImage
    {
        $down = $this->imageDownloadService->download($input->url);
        $start = microtime(true);
        $read = $this->imageManager->read($down);
        $buffer = $read->scale(width: $input->width)->toWebp($input->q)->toString();

        return new ResizedImage($buffer, 'image/webp', microtime(true) - $start);
    }
}