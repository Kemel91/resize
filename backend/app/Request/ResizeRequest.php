<?php

declare(strict_types=1);

namespace App\Request;

use App\Processors\Dto\ResizeInput;
use App\Processors\Enums\FormatEnum;
use Hyperf\Validation\Request\FormRequest;

class ResizeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'url' => 'required|url',
            'width' => 'required|integer',
            'height' => 'integer',
            'q' => 'integer|between:0,100',
            'cache' => 'integer',
            'format' => 'string',
        ];
    }

    public function dto(): ResizeInput
    {
        return new ResizeInput(
            $this->input('url'),
            (int) $this->input('width', 600),
            $this->has('height') ? (int) $this->input('height') : null,
            $this->has('q') ? (int) $this->input('q') : null,
            $this->has('cache') ? (int) $this->input('cache') : 60 * 60,
            $this->has('format') ? FormatEnum::fromExtension($this->input('format')) : null,
        );
    }
}
