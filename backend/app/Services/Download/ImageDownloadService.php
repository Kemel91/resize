<?php
declare(strict_types=1);

namespace App\Services\Download;

use App\Helpers\HashHelper;
use Hyperf\Guzzle\ClientFactory;
use Psr\SimpleCache\CacheInterface;

readonly class ImageDownloadService
{
    public function __construct(
        private CacheInterface $cache,
        private ClientFactory $clientFactory,
    )
    {
    }

    public function download(string $url): string
    {
        $key = HashHelper::hash($url);
        if ($image = $this->cache->get($key)) {
            return $image;
        }

        $client = $this->clientFactory->create();
        try {
            $image = $client->get($url)->getBody()->getContents();
        } catch (\Throwable) {
            throw NotFoundImageException::make();
        }

        $this->cache->set($key, $image, 5 * 60);

        return $image;
    }
}