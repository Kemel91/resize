<?php
declare(strict_types=1);

namespace App\Processors\Dto;

use App\Processors\Enums\FormatEnum;
use Hyperf\HttpMessage\Upload\UploadedFile;

class ResizeInput
{
    private const int MAX_CACHE = 60 * 60 * 24;

    public int $cache = 0;

    public function __construct(
        public ?string $url,
        public ?UploadedFile $image,
        public int $width,
        public ?int $height = null,
        public ?int $q = 75,
        int $cache = 0,
        public ?FormatEnum $format = null,
    )
    {
        if ($cache < 0) {
            $cache = 0;
        } elseif ($cache > self::MAX_CACHE) {
            $cache = self::MAX_CACHE;
        }

        $this->cache = $cache;
    }

    public function key(): string
    {
        $filePath = '';
        if ($this->image) {
            $filePath = $this->image->getPath();
        }

        return $this->url.$filePath.$this->width.$this->q.$this->format?->value;
    }

    public function hasFile(): bool
    {
        return $this->image !== null;
    }
}