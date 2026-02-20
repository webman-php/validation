<?php
declare(strict_types=1);

namespace Webman\Validation\Tests;

use PHPUnit\Framework\TestCase;
use support\validation\Validator as SupportValidator;
use support\validation\ValidationException as SupportValidationException;
use Webman\Validation\Validator as CoreValidator;

final class ValidatorExceptionTest extends TestCase
{
    public function test_core_validator_uses_support_validation_exception_by_default(): void
    {
        $this->expectException(SupportValidationException::class);

        CoreValidator::make(
            ['name' => null],
            ['name' => 'required']
        )->validate();
    }

    public function test_support_validator_uses_support_validation_exception_by_default(): void
    {
        $this->expectException(SupportValidationException::class);

        SupportValidator::make(
            ['name' => null],
            ['name' => 'required']
        )->validate();
    }

    public function test_exception_override_uses_custom_exception(): void
    {
        $this->expectException(\RuntimeException::class);

        CoreValidator::make(
            ['name' => null],
            ['name' => 'required']
        )
            ->withException(\RuntimeException::class)
            ->validate();
    }
}

