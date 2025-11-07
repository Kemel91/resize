<?php

namespace App\Exception\Http;

use function json_encode;

class HttpResponseException extends \Exception implements \JsonSerializable
{
    public function __construct(public array $response, int $code = 404, ?\Throwable $previous = null)
    {
        $message = $previous ? $previous->getMessage() : '';
        parent::__construct($message, $code);
    }

    public function jsonSerialize(): string
    {
        return json_encode($this->response, JSON_UNESCAPED_UNICODE);
    }
}