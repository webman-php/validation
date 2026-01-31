<?php
declare(strict_types=1);

namespace Webman\Validation\Tests;

use PHPUnit\Framework\TestCase;
use support\validation\ValidationException;
use support\validation\Validator as SupportValidator;

final class ValidatorPublicMethodsTest extends TestCase
{
    public function testOverrideRulesMethodIsUsed(): void
    {
        $validator = OverrideRulesValidator::make(['email' => 'not-an-email']);

        $this->expectException(ValidationException::class);
        $validator->validate();
    }

    public function testOverrideMessagesMethodIsUsed(): void
    {
        $validator = OverrideMessagesValidator::make([]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Custom required message');
        $validator->validate();
    }

    public function testOverrideAttributesMethodIsUsed(): void
    {
        $validator = OverrideAttributesValidator::make([]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('E-mail is required');
        $validator->validate();
    }

    public function testOverrideDataMethodIsUsed(): void
    {
        $validated = OverrideDataValidator::make(['email' => 'not-an-email'])->validate();
        $this->assertSame(['email' => 'user@example.com'], $validated);
    }

    public function testReuseRulesFromAnotherValidatorViaPublicMethod(): void
    {
        $validated = ReuseRulesValidator::make(['email' => 'user@example.com'])->validate();
        $this->assertSame(['email' => 'user@example.com'], $validated);
    }
}

final class OverrideRulesValidator extends SupportValidator
{
    protected array $rules = [
        'dummy' => 'nullable',
    ];

    public function rules(): array
    {
        return [
            'email' => 'required|email',
        ];
    }
}

final class OverrideMessagesValidator extends SupportValidator
{
    protected array $rules = [
        'email' => 'required',
    ];

    public function messages(): array
    {
        return [
            'email.required' => 'Custom required message',
        ];
    }
}

final class OverrideAttributesValidator extends SupportValidator
{
    protected array $rules = [
        'email' => 'required',
    ];

    public function messages(): array
    {
        return [
            'email.required' => ':attribute is required',
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => 'E-mail',
        ];
    }
}

final class OverrideDataValidator extends SupportValidator
{
    protected array $rules = [
        'email' => 'required|email',
    ];

    public function data(): array
    {
        return [
            'email' => 'user@example.com',
        ];
    }
}

final class RulesSourceValidator extends SupportValidator
{
    protected array $rules = [
        'email' => 'required|email',
    ];
}

final class ReuseRulesValidator extends SupportValidator
{
    protected array $rules = [
        'dummy' => 'nullable',
    ];

    public function rules(): array
    {
        return RulesSourceValidator::make($this->data)->rules();
    }
}

