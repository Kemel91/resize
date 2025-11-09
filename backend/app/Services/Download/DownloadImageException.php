<?php

namespace App\Services\Download;

use App\Exception\Http\JsonErrorResponseException;

class DownloadImageException extends JsonErrorResponseException
{
    public static function makeNotFound(): self
    {
        return new self(['code' => 20, 'message' => 'Not Found Image'], 200);
    }

    public static function makeLargeSize(): self
    {
        return new self(['code' => 21, 'message' => 'Large Size Image'], 200);
    }
}