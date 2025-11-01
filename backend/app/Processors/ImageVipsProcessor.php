<?php
declare(strict_types=1);

namespace App\Processors;

use App\Helpers\HashHelper;
use App\Services\ImageDownloadService;
use Co\Http\Client;
use Hyperf\Cache\CacheManager;
use Jcupitt\Vips;

readonly class ImageVipsProcessor implements ImageProcessorInterface
{
    public function __construct(
        private ImageDownloadService $imageDownloadService,
        private CacheManager $cache,
    )
    {
    }

    public function process(string $url, int $width = 600, int $q = 85, bool $cache = false): ResizedImage
    {
        $hash = HashHelper::hash($url.$width.$q);
        if ($cache && $cached = $this->cache->getDriver()->get($hash)) {
            return new ResizedImage($cached);
        }

        $down = $this->imageDownloadService->download($url);
        $start = microtime(true);
        $image = Vips\Image::thumbnail_buffer($down, $width);
        $buffer = $image->writeToBuffer('.jpg');

        if ($cache) {
            $this->cache->getDriver()->set($hash, $buffer, 5 * 60);
        }

        return new ResizedImage($buffer, 'image/jpeg', microtime(true) - $start);
    }

}