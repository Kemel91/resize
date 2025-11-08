<?php
declare(strict_types=1);

namespace App\Processors;

use App\Helpers\HashHelper;
use App\Processors\Events\ResizedEvent;
use App\Processors\Exceptions\UnsupportedFormatImageException;
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
        $hash = HashHelper::hash($input->url.$input->width.$input->q.$input->format?->value);
        if ($input->cache && $cached = $this->cache->get($hash)) {
            return new ResizedImage($cached);
        }

        $image = $this->imageDownloadService->download($input->url);

        $start = microtime(true);

        $imageBuffer = Vips\Image::newFromBuffer($image->content);
        if ($imageBuffer->width > $input->width) {
            $imageCrop = $imageBuffer->thumbnail_image($input->width);
        } else {
            $imageCrop = $imageBuffer;
        }

        $imageFormat = $image->getFormat();
        if ($input->format === null || $input->format === $imageFormat) {
            if ($imageBuffer === $imageCrop) {
                $buffer = $image->content;
            } else {
                $buffer = $imageCrop->writeToBuffer('.' . $imageFormat->getExtension());
            }
        } else {
            $buffer = $this->convertFormat($imageCrop, $input->format);
        }

        if ($input->cache) {
            $this->cache->set($hash, $buffer, $input->cache);
        }

        $this->eventDispatcher->dispatch(new ResizedEvent(
            $input->url,
            $image->getSize(),
            strlen($buffer),
        ));

        $mimeType = $input->format?->getMimeType() ?? $imageFormat->getMimeType();

        return new ResizedImage($buffer, $mimeType, microtime(true) - $start);
    }

    private function convertFormat(Vips\Image $image, FormatEnum $format): string
    {
        return match ($format) {
            FormatEnum::JPEG => $image->jpegsave_buffer(),
            FormatEnum::PNG => $image->pngsave_buffer(),
            FormatEnum::GIF => $image->gifsave_buffer(),
            FormatEnum::WEBP => $image->webpsave_buffer(),
            default => throw UnsupportedFormatImageException::make(),
        };
    }

}