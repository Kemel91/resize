<?php

namespace App\Services\Download;

use App\Exception\Http\HttpResponseException;

class NotFoundImageException extends HttpResponseException
{
    public static function make(): self
    {
        return new self(['code' => 404, 'message' => 'Not Found Image'], 200);
    }
}