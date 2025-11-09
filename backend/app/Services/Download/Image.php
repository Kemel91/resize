<?php
declare(strict_types=1);

namespace App\Services\Download;

use App\Processors\Enums\FormatEnum;

use function strlen;
use const PATHINFO_EXTENSION;

final readonly class Image
{
    public function __construct(
        public string $url,
        public string $content,
    )
    {
    }

    public function getSize(): int
    {
        return strlen($this->content);
    }

    private function getExtension(): string
    {
        return pathinfo($this->url, PATHINFO_EXTENSION);
    }

    public function getFormat(): FormatEnum
    {
        return FormatEnum::fromExtension($this->getExtension());
    }
}