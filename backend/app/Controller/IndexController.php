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
use App\Processors\InterventionProcessor;
use App\Request\ResizeRequest;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\MessageInterface;

class IndexController
{
    public function index(): array
    {
        return [
            'message' => "Hello from ImageKit. Use api.imagekit.ru/resize for resize images.",
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
            ->withHeader('Resized-time', round($resizedImage->time ?? 0.0, 3))
            ->withBody(new SwooleStream($resizedImage->content));
    }

    public function resize2(
        ResizeRequest $request,
        ResponseInterface $response,
        InterventionProcessor $interventionProcessor,
    ): MessageInterface|ResponseInterface
    {
        $resizedImage = $interventionProcessor->process($request->dto());

        return $response->withHeader('Content-Type', $resizedImage->mimeType)
            ->withHeader('Resized-time', round($resizedImage->time ?? 0.0, 3))
            ->withBody(new SwooleStream($resizedImage->content));
    }
}
