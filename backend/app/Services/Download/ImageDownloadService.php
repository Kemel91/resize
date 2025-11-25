<?php
declare(strict_types=1);

namespace App\Services\Download;

use App\Helpers\HashHelper;
use App\Processors\Dto\ResizeInput;
use Hyperf\Guzzle\ClientFactory;
use Psr\Http\Message\StreamInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

readonly class ImageDownloadService
{
    public function __construct(
        private CacheInterface $cache,
        private ClientFactory $clientFactory,
    )
    {
    }

    /**
     * @throws InvalidArgumentException
     * @throws DownloadImageException
     */
    public function download(ResizeInput $input): Image
    {
        if ($input->hasFile()) {
            return new Image(
                $input->image->getClientFilename(),
                $input->image->getStream()->getContents(),
            );
        }

        $url = $input->url;
        $key = HashHelper::hash($url);
        if ($image = $this->cache->get($key)) {
            return new Image($url, $image);
        }

        $client = $this->clientFactory->create();
        try {
            $body = $client->get($url)->getBody();
        } catch (\Throwable) {
            throw DownloadImageException::makeNotFound();
        }

        $this->checkResponse($body);
        $image = $body->getContents();

        $this->cache->set($key, $image, 5 * 60);

        return new Image($url, $image);
    }

    /**
     * @throws DownloadImageException
     */
    private function checkResponse(StreamInterface $stream): void
    {
        $maxSize = 1024 * 1024 * 10;
        if ($stream->getSize() > $maxSize) {
            throw DownloadImageException::makeLargeSize();
        }
    }
}