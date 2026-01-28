<?php
declare(strict_types=1);

namespace Webman\Validation;

interface ValidationSetInterface
{
    public static function rules(string $scene = 'default'): array;

    public static function messages(string $scene = 'default'): array;

    public static function attributes(string $scene = 'default'): array;
}
