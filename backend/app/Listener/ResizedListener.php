<?php
declare(strict_types=1);

namespace App\Listener;

use App\Processors\Events\ResizedEvent;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Redis\Redis;

#[Listener]
readonly class ResizedListener implements ListenerInterface
{
    public function __construct(private Redis $redis)
    {
    }

    public function listen(): array
    {
        return [
            ResizedEvent::class,
        ];
    }

    public function process(object $event): void
    {
        $domain = parse_url($event->url, PHP_URL_HOST);
        $key = sprintf('resized_host:%s', $domain);
        $this->redis->incr($key);
    }
}
