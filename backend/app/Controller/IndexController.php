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

use Hyperf\HttpMessage\Stream\SwooleStream;
use App\Processors\ImageProcessor;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\MessageInterface;

class IndexController extends AbstractController
{
    public function __construct(private ImageProcessor $imageProcessor)
    {
    }

    public function index(): array
    {
        $user = $this->request->input('user', 'Hyperf');
        $method = $this->request->getMethod();

        return [
            'method' => $method,
            'message' => "Hello {$user}.",
        ];
    }

    public function resize(ResponseInterface $response): MessageInterface|ResponseInterface
    {
        $url = $this->request->input('url', 'https://images.biblioglobus.ru/bgagentdb/images/sletat/143254/143254_0.jpg');
        $width = $this->request->input('width', 600);
        $q = $this->request->input('q', 85);
        [$image, $time] = $this->imageProcessor->test($url, (int) $width, (int) $q);

        return $response->withHeader('Content-Type', 'image/webp')
            ->withBody(new SwooleStream($image));
    }
}
