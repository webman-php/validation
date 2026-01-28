<?php
declare(strict_types=1);

namespace Webman\Validation\Exceptions;

use Illuminate\Validation\Validator as IlluminateValidator;
use Webman\Exception\BusinessException;

class ValidationException extends BusinessException
{
    private array $errors = [];

    public function __construct(string $message = 'Validation failed', array $errors = [], int $code = 422)
    {
        parent::__construct($message, $code);
        $this->errors = $errors;
        $this->data(['errors' => $errors]);
    }

    public static function fromValidator(IlluminateValidator $validator): static
    {
        $errors = $validator->errors()->toArray();
        $message = $validator->errors()->first() ?: 'Validation failed';
        return new static($message, $errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function first(): string
    {
        foreach ($this->errors as $messages) {
            if (is_array($messages) && $messages) {
                return (string)$messages[0];
            }
        }
        return $this->getMessage();
    }
}
