<?php
declare(strict_types=1);

namespace App\Processors;

use App\Helpers\HashHelper;
use App\Processors\Dto\ResizedImage;
use App\Processors\Dto\ResizeInput;
use App\Processors\Enums\FormatEnum;
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

        $imageDownload = $this->imageDownloadService->download($input->url);

        $start = microtime(true);

        $imageBuffer = Vips\Image::newFromBuffer($imageDownload->content);
        $imageCrop = $this->cropImage($imageBuffer, $input);

        $imageFormat = $imageDownload->getFormat();
        if ($input->format === null || $input->format === $imageFormat) {
            if ($imageBuffer === $imageCrop) {
                $buffer = $imageDownload->content;
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
            $imageDownload->getSize(),
            strlen($buffer),
        ));

        $mimeType = $input->format?->getMimeType() ?? $imageFormat->getMimeType();

        return new ResizedImage($buffer, $mimeType, microtime(true) - $start);
    }

    private function cropImage(Vips\Image $image, ResizeInput $input): Vips\Image
    {
        if ($image->width > $input->width) {
            return $image->thumbnail_image($input->width);
        }

        return $image;
    }

    /**
     * @throws UnsupportedFormatImageException
     */
    private function convertFormat(Vips\Image $image, FormatEnum $format): string
    {
        return match ($format) {
            FormatEnum::JPEG => $image->jpegsave_buffer(),
            FormatEnum::PNG => $image->pngsave_buffer(),
            FormatEnum::GIF => $image->gifsave_buffer(),
            FormatEnum::WEBP => $image->webpsave_buffer(),
            FormatEnum::TIFF => $image->tiffsave_buffer(),
            FormatEnum::HEIF => $image->heifsave_buffer(),
            default => throw UnsupportedFormatImageException::make(),
        };
    }

}