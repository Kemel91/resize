<?php
declare(strict_types=1);

namespace App\Processors;

use App\Services\ImageDownloadService;
use Co\Http\Client;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Vips\Driver as VipsDriver;

class ImageInterventionProcessor implements ImageProcessorInterface
{
    private ImageManager $imageManager;

    public function __construct(
        private readonly ImageDownloadService $imageDownloadService,
    )
    {
        $this->imageManager = ImageManager::withDriver(VipsDriver::class);
    }

    public function process(string $url, int $width = 600, int $q = 85, bool $cache = false): ResizedImage
    {
        $down = $this->imageDownloadService->download($url);
        $start = microtime(true);
        $read = $this->imageManager->read($down);
        $buffer = $read->scale(width: $width)->toWebp($q)->toString();

        return new ResizedImage($buffer, 'image/webp', microtime(true) - $start);
    }
}