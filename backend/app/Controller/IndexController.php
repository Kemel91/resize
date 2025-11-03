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
use App\Request\ResizeRequest;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\MessageInterface;

class IndexController
{
    public function index(RequestInterface $request): array
    {
        $user = $request->input('user', 'Hyperf');
        $method = $request->getMethod();

        return [
            'method' => $method,
            'message' => "Hello {$user}.",
        ];
    }

    public function resize(
        ResizeRequest $request,
        ResponseInterface $response,
        ImageVipsProcessor $imageVipsProcessor,
    ): MessageInterface|ResponseInterface
    {
        $resizedImage = $imageVipsProcessor->process($request->dto());

        return $response->withHeader('Content-Type', $resizedImage->mimeType)
            ->withHeader('Resized-time', $resizedImage->time)
            ->withBody(new SwooleStream($resizedImage->content));
    }
}
