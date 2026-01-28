<?php
declare(strict_types=1);

namespace Webman\Validation;

use Illuminate\Validation\Validator as IlluminateValidator;
use support\validation\ValidationException;

final class ValidationResult
{
    public function __construct(private IlluminateValidator $validator)
    {
    }

    public function validate(): array
    {
        if ($this->validator->fails()) {
            throw ValidationException::fromValidator($this->validator);
        }

        return $this->validator->validated();
    }

    public function errors(): array
    {
        return $this->validator->errors()->toArray();
    }

    public function first(): string
    {
        return $this->validator->errors()->first() ?: '';
    }
}
