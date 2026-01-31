<?php
declare(strict_types=1);

namespace Webman\Validation\Tests;

use PHPUnit\Framework\TestCase;
use support\validation\Validator;
use support\validation\ValidationException;
use Webman\Validation\Exceptions\ValidationException as BaseValidationException;

final class ValidatorTest extends TestCase
{
    public function testValidatePassReturnsValidatedData(): void
    {
        $validated = Validator::make(
            ['email' => 'user@example.com'],
            ['email' => 'required|email']
        )->validate();

        $this->assertSame(['email' => 'user@example.com'], $validated);
    }

    public function testValidateFailThrowsConfiguredExceptionWithFirstMessage(): void
    {
        try {
            Validator::make(
                ['email' => 'not-an-email'],
                ['email' => 'required|email'],
                ['email.email' => 'Email invalid']
            )->validate();
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            $this->assertSame('Email invalid', $exception->getMessage());
        }
    }

    public function testValidateFailUsesConfigExceptionClass(): void
    {
        $this->setValidationExceptionConfig(ConfigValidationException::class);

        try {
            Validator::make(
                ['email' => 'not-an-email'],
                ['email' => 'required|email']
            )->validate();
            $this->fail('Expected ConfigValidationException was not thrown.');
        } catch (ConfigValidationException $exception) {
            $this->assertSame('The email field must be a valid email address.', $exception->getMessage());
        } finally {
            $this->setValidationExceptionConfig(ValidationException::class);
        }
    }

    private function setValidationExceptionConfig(string $exceptionClass): void
    {
        validation_test_set_config([
            'plugin' => [
                'webman' => [
                    'validation' => [
                        'app' => [
                            'exception' => $exceptionClass,
                        ],
                    ],
                ],
            ],
        ]);
    }
}

final class ConfigValidationException extends BaseValidationException
{
}

