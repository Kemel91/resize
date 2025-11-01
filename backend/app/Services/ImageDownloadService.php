<?php

namespace App\Services;

use App\Helpers\HashHelper;
use Hyperf\Cache\CacheManager;
use Hyperf\Guzzle\ClientFactory;

readonly class ImageDownloadService
{
    public function __construct(
        private CacheManager $cache,
        private ClientFactory $clientFactory,
    )
    {
    }

    public function download(string $url): string
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
}