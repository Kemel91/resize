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
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Drivers\Vips\Driver as VipsDriver;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\SimpleCache\CacheInterface;

use function microtime;
use function strlen;

readonly class InterventionProcessor implements ImageProcessorInterface
{
    private ImageManager $imageManager;

    public function __construct(
        private ImageDownloadService $imageDownloadService,
        private CacheInterface $cache,
        private EventDispatcherInterface $eventDispatcher,
    )
    {
        $this->imageManager = ImageManager::withDriver(Driver::class);
    }

    public function process(ResizeInput $input): ResizedImage
    {
        $hash = HashHelper::hash(__CLASS__.$input->url.$input->width.$input->q.$input->format?->value);
        if ($input->cache && $cached = $this->cache->get($hash)) {
            return new ResizedImage($cached);
        }

        $imageDownload = $this->imageDownloadService->download($input->url);

        $start = microtime(true);

        $imageBuffer = $this->imageManager->read($imageDownload->content);

        $imageCrop = $this->cropImage($imageBuffer, $input);

        $imageFormat = $imageDownload->getFormat();
        if ($input->format === null || $input->format === $imageFormat) {
            if ($imageBuffer === $imageCrop) {
                $buffer = $imageDownload->content;
            } else {
                $buffer = $imageCrop->encode()->toString();
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

    private function cropImage(ImageInterface $image, ResizeInput $input): ImageInterface
    {
        if ($image->width() > $input->width) {
            return $image->scale($input->width);
        }

        return $image;
    }

    /**
     * @throws UnsupportedFormatImageException
     */
    private function convertFormat(ImageInterface $image, FormatEnum $format): string
    {
        $converted = match ($format) {
            FormatEnum::JPEG => $image->toJpeg(),
            FormatEnum::PNG => $image->toPng(),
            FormatEnum::GIF => $image->toGif(),
            FormatEnum::WEBP => $image->toWebp(),
            FormatEnum::TIFF => $image->toTiff(),
            FormatEnum::HEIF => $image->toHeic(),
            default => throw UnsupportedFormatImageException::make(),
        };

        return $converted->toString();
    }

}