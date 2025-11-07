<?php
declare(strict_types=1);

namespace App\Processors;

use App\Helpers\HashHelper;
use App\Processors\Events\ResizedEvent;
use App\Services\Download\ImageDownloadService;
use Jcupitt\Vips;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\SimpleCache\CacheInterface;
use function microtime;
use function strlen;

readonly class ImageVipsProcessor implements ImageProcessorInterface
{
    public function __construct(
        private ImageDownloadService $imageDownloadService,
        private CacheInterface $cache,
        private EventDispatcherInterface $eventDispatcher,
    )
    {
    }

    public function process(ResizeInput $input): ResizedImage
    {
        $hash = HashHelper::hash($input->url.$input->width.$input->q);
        if ($input->cache && $cached = $this->cache->get($hash)) {
            return new ResizedImage($cached);
        }

        $down = $this->imageDownloadService->download($input->url);
        $start = microtime(true);
        $image = Vips\Image::thumbnail_buffer($down, $input->width);
        $buffer = $image->writeToBuffer('.jpg');

        if ($input->cache) {
            $this->cache->set($hash, $buffer, 5 * 60);
        }

        $this->eventDispatcher->dispatch(new ResizedEvent(
            $input->url,
            strlen($down),
            strlen($buffer),
        ));

        return new ResizedImage($buffer, 'image/jpeg', microtime(true) - $start);
    }

}