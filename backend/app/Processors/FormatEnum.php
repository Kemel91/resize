<?php

namespace App\Processors;

enum FormatEnum: string
{
    case JPEG = 'jpeg';
    case PNG = 'png';
    case GIF = 'gif';
    case WEBP = 'webp';
    case AVIF = 'avif';
    case TIFF = 'tiff';
    case HEIF = 'heif';

    public function getMimeType(): string
    {
        return match ($this) {
            self::JPEG => 'image/jpeg',
            self::PNG => 'image/png',
            self::GIF => 'image/gif',
            self::WEBP => 'image/webp',
            self::AVIF => 'image/avif',
            self::TIFF => 'image/tiff',
            self::HEIF => 'image/heif',
        };
    }

    public function getExtension(): string
    {
        return $this->value;
    }

    public static function fromExtension(string $extension): self
    {
        $extension = strtolower($extension);

        if ($extension === 'jpg') {
            return self::JPEG;
        }

        foreach (self::cases() as $format) {
            if ($format->value === $extension) {
                return $format;
            }
        }

        throw new \ValueError(sprintf('"%s" is not a valid extension for enum %s', $extension, self::class));
    }
}
