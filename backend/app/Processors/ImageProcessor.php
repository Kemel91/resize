<?php
declare(strict_types=1);

namespace App\Processors;

use App\Helpers\HashHelper;
use Co\Http\Client;
use Hyperf\Cache\CacheManager;
use Hyperf\Guzzle\ClientFactory;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Vips\Driver as VipsDriver;
use Jcupitt\Vips;

class ImageProcessor
{
    private ImageManager $imageManager;

    public function __construct(
        private readonly CacheManager $cache,
        private readonly ClientFactory $clientFactory,
    )
    {
        $this->imageManager = ImageManager::withDriver(VipsDriver::class);
    }

    public function test(string $url, int $width = 600, $q = 85,): array
    {
        $down = $this->download($url);
        $start = microtime(true);
        $read = $this->imageManager->read($down);
        $buffer = $read->scale(width: $width)->toWebp($q)->toString();

        return [$buffer, microtime(true) - $start];
    }

    private function download(string $url): string
    {
        $key = HashHelper::hash($url);
        if ($image = $this->cache->getDriver()->get($key)) {
            return $image;
        }

        $client = $this->clientFactory->create();
        $image = $client->get($url)->getBody()->getContents();

        $this->cache->getDriver()->set($key, $image, 30);

        return $image;
    }

    public function processImage(string $url, int $width, int $height, int $quality): string
    {
        $manager = ImageManager::withDriver(VipsDriver::class);
        $manager->read($url);
        // Загрузка изображения
        $imageData = $this->downloadImage($url);

        // Обработка через libvips
        return $this->processWithVips($imageData, $width, $height, $quality);
    }

    private function downloadImage(string $url): string
    {
        $client = new Swoole\Coroutine\Http\Client(
            parse_url($url, PHP_URL_HOST),
            parse_url($url, PHP_URL_PORT) ?? 80
        );
        $client2 = new Client();
        $client2->download();

        $client->set([
            'timeout' => 10,
            'follow_location' => 5,
            'http_compression' => true,
        ]);

        $client->setHeaders([
            'User-Agent' => 'ImageResizer/1.0',
            'Accept' => 'image/*'
        ]);

        $path = parse_url($url, PHP_URL_PATH) . (parse_url($url, PHP_URL_QUERY) ? '?' . parse_url($url, PHP_URL_QUERY) : '');
        $client->get($path);

        if ($client->statusCode !== 200) {
            throw new Exception("Failed to download image: HTTP {$client->statusCode}");
        }

        $body = $client->getBody();
        $client->close();

        if (empty($body)) {
            throw new Exception("Empty image response");
        }

        return $body;
    }

    private function processWithVips(string $imageData, int $width, int $height, int $quality): string
    {
        try {
            // Создание изображения из буфера
            $image = Vips\Image::newFromBuffer($imageData);

            // Автоматический поворот по EXIF
            $image = $this->autoRotate($image);

            // Умный ресайз с сохранением пропорций
            $resized = $this->smartResize($image, $width, $height);

            // Конвертация в JPEG с оптимизацией
            $output = $resized->writeToBuffer('.jpg', [
                'Q' => $quality,
                'optimize_coding' => true,
                'interlace' => true,
                'strip' => true, // Удаление метаданных
                'subsample_mode' => 'auto'
            ]);

            return $output;

        } catch (Exception $e) {
            throw new Exception("Vips processing failed: " . $e->getMessage());
        }
    }

    private function autoRotate($image)
    {
        // Автоповорот на основе EXIF ориентации
        try {
            $orientation = $image->get('exif-ifd0-Orientation') ?? 1;

            switch ($orientation) {
                case 2:
                    return $image->flip('horizontal');
                case 3:
                    return $image->rot180();
                case 4:
                    return $image->flip('vertical');
                case 5:
                    return $image->rot90()->flip('horizontal');
                case 6:
                    return $image->rot90();
                case 7:
                    return $image->rot270()->flip('horizontal');
                case 8:
                    return $image->rot270();
                default:
                    return $image;
            }
        } catch (Exception $e) {
            // Если EXIF недоступен, возвращаем оригинал
            return $image;
        }
    }

    private function smartResize($image, int $targetWidth, int $targetHeight)
    {
        $originalWidth = $image->width;
        $originalHeight = $image->height;

        // Если изображение меньше целевых размеров, не увеличиваем
        if ($originalWidth <= $targetWidth && $originalHeight <= $targetHeight) {
            return $image;
        }
        $ratio = min($targetWidth / $originalWidth, $targetHeight / $originalHeight);

        $newWidth = (int)round($originalWidth * $ratio);
        $newHeight = (int)round($originalHeight * $ratio);

        // Ресайз с высококачественной интерполяцией
        return $image;
    }
}