<?php
declare(strict_types=1);

namespace Webman\Validation\Tests;

use PHPUnit\Framework\TestCase;
use support\validation\Validator;
use support\validation\ValidationException;
use Webman\Validation\Exception\ValidationException as BaseValidationException;

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

    public function testCustomValidatorWithoutScenesUsesAllRulesByDefault(): void
    {
        $validated = UserValidatorWithoutScenes::make([
            'email' => 'user@example.com',
        ])->validate();

        $this->assertSame(['email' => 'user@example.com'], $validated);
    }

    // ───── Closure as validation rule tests ─────

    public function testClosureRuleValidationPasses(): void
    {
        $validated = Validator::make(
            ['age' => 20],
            ['age' => ['required', 'integer', function ($attribute, $value, $fail) {
                if ($value < 18) {
                    $fail("The {$attribute} must be at least 18.");
                }
            }]]
        )->validate();

        $this->assertSame(['age' => 20], $validated);
    }

    public function testClosureRuleValidationFails(): void
    {
        try {
            Validator::make(
                ['age' => 15],
                ['age' => ['required', 'integer', function ($attribute, $value, $fail) {
                    if ($value < 18) {
                        $fail("The {$attribute} must be at least 18.");
                    }
                }]]
            )->validate();
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            $this->assertSame('The age must be at least 18.', $exception->getMessage());
        }
    }

    public function testMultipleClosureRulesValidation(): void
    {
        $validated = Validator::make(
            ['code' => 'prefix_abc'],
            ['code' => ['required', 'string',
                function ($attribute, $value, $fail) {
                    if (!str_starts_with($value, 'prefix_')) {
                        $fail("The {$attribute} must start with prefix_.");
                    }
                },
                function ($attribute, $value, $fail) {
                    if (strlen($value) < 5) {
                        $fail("The {$attribute} must be at least 5 characters.");
                    }
                },
            ]]
        )->validate();

        $this->assertSame(['code' => 'prefix_abc'], $validated);
    }

    public function testClosureRuleInCustomValidator(): void
    {
        $validated = ClosureRuleValidator::make(['score' => 85])->validate();
        $this->assertSame(['score' => 85], $validated);
    }

    public function testClosureRuleInCustomValidatorFails(): void
    {
        try {
            ClosureRuleValidator::make(['score' => 150])->validate();
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('score', $exception->getMessage());
        }
    }

    // ───── Helpers ─────

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

final class UserValidatorWithoutScenes extends Validator
{
    protected array $rules = [
        'email' => 'required|email',
    ];
}

final class ClosureRuleValidator extends Validator
{
    protected array $rules = [
        'score' => 'required|integer',
    ];

    public function rules(): array
    {
        return [
            'score' => ['required', 'integer', function ($attribute, $value, $fail) {
                if ($value < 0 || $value > 100) {
                    $fail("The {$attribute} must be between 0 and 100.");
                }
            }],
        ];
    }
}

