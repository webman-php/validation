<?php
declare(strict_types=1);

namespace Webman\Validation;

use InvalidArgumentException;
use Illuminate\Validation\Validator as IlluminateValidator;
use Webman\Validation\Exceptions\ValidationException as BaseValidationException;
use support\validation\ValidationException;

final class ValidationResult
{
    public function __construct(
        private IlluminateValidator $validator,
        private ?string $exceptionClass = null
    )
    {
    }

    public function validate(): array
    {
        if ($this->validator->fails()) {
            $exceptionClass = $this->resolveExceptionClass();
            throw $exceptionClass::fromValidator($this->validator);
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

    private function resolveExceptionClass(): string
    {
        $exceptionClass = $this->exceptionClass;
        if ($exceptionClass === null || $exceptionClass === '') {
            $exceptionClass = config(
                'plugin.webman.validation.app.exception',
                ValidationException::class
            );
        }

        if (!is_string($exceptionClass) || $exceptionClass === '') {
            throw new InvalidArgumentException('Validation exception must be a non-empty class string.');
        }
        if (!class_exists($exceptionClass)) {
            throw new InvalidArgumentException("Validation exception class not found: {$exceptionClass}");
        }

        return $exceptionClass;
    }
}
