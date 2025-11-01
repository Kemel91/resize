<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Controller;

use App\Processors\ImageVipsProcessor;
use Hyperf\HttpMessage\Stream\SwooleStream;
use App\Processors\ImageInterventionProcessor;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\MessageInterface;

class IndexController extends AbstractController
{
    public function index(): array
    {
        $user = $this->request->input('user', 'Hyperf');
        $method = $this->request->getMethod();

        return [
            'method' => $method,
            'message' => "Hello {$user}.",
        ];
    }

    public function resize(
        ResponseInterface $response,
        //ImageInterventionProcessor $imageProcessor,
        ImageVipsProcessor $imageVipsProcessor,
    ): MessageInterface|ResponseInterface
    {
        $url = $this->request->input('url', 'https://images.biblioglobus.ru/bgagentdb/images/sletat/143254/143254_0.jpg');
        $width = $this->request->input('width', 600);
        $q = $this->request->input('q', 85);
        $cache = $this->request->input('cache', false);
       // [$image, $time] = $imageProcessor->process($url, (int) $width, (int) $q);
        $resizedImage = $imageVipsProcessor->process($url, (int) $width, (int) $q, (bool) $cache);

        return $response->withHeader('Content-Type', $resizedImage->mimeType)
            ->withHeader('Resized-time', $resizedImage->time)
            ->withBody(new SwooleStream($resizedImage->content));
    }
}
