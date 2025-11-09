<?php

namespace App\Processors\Exceptions;

use App\Exception\Http\JsonErrorResponseException;
use App\Processors\Enums\FormatEnum;

class UnsupportedFormatImageException extends JsonErrorResponseException
{
    public static function make(): self
    {
        $available = array_map(fn(FormatEnum $format) => $format->value, FormatEnum::cases());
        $message = 'Not supported image convert. Available formats: '.implode(', ', $available);

        return new self(['code' => 1, 'message' => $message], 200);
    }
}