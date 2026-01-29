<?php
declare(strict_types=1);

namespace Webman\Validation;

use Webman\Validation\Factory\ValidationFactory;

class Validator
{
    public static function make(
        array $data,
        array $rules,
        array $messages = [],
        array $attributes = [],
        ?string $exceptionClass = null
    ): ValidationResult {
        $factory = ValidationFactory::getFactory();
        $validator = $factory->make($data, $rules, $messages, $attributes);
        return new ValidationResult($validator, $exceptionClass);
    }
}
