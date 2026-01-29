<?php
declare(strict_types=1);

namespace Webman\Validation\Tests;

use Illuminate\Validation\Rule as IlluminateRule;
use Illuminate\Validation\Rules\In;
use PHPUnit\Framework\TestCase;
use support\validation\ValidationException;
use support\validation\Rule;
use support\validation\Validator;

final class RuleTest extends TestCase
{
    public function testRuleExtendsIlluminateRule(): void
    {
        $this->assertTrue(is_subclass_of(Rule::class, IlluminateRule::class));
    }

    public function testRuleInProxy(): void
    {
        $rule = Rule::in(['a', 'b']);
        $this->assertInstanceOf(In::class, $rule);
    }

    public function testRuleInPassesValidation(): void
    {
        $validated = Validator::make(
            ['status' => 'enabled'],
            ['status' => ['required', Rule::in(['enabled', 'disabled'])]]
        )->validate();

        $this->assertSame(['status' => 'enabled'], $validated);
    }

    public function testRuleInFailsValidation(): void
    {
        try {
            Validator::make(
                ['status' => 'unknown'],
                ['status' => ['required', Rule::in(['enabled', 'disabled'])]],
                ['status.in' => 'Status invalid']
            )->validate();
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            $this->assertSame('Status invalid', $exception->getMessage());
            $this->assertSame(['status' => ['Status invalid']], $exception->errors());
        }
    }

    public function testRuleAnyOfPassesValidation(): void
    {
        $validated = Validator::make(
            ['username' => 'user@example.com'],
            ['username' => [
                'required',
                Rule::anyOf([
                    ['string', 'email'],
                    ['string', 'alpha_dash', 'min:6'],
                ]),
            ]]
        )->validate();

        $this->assertSame(['username' => 'user@example.com'], $validated);
    }

    public function testRuleAnyOfFailsValidation(): void
    {
        try {
            Validator::make(
                ['username' => 'a?'],
                ['username' => [
                    'required',
                    Rule::anyOf([
                        ['string', 'email'],
                        ['string', 'alpha_dash', 'min:6'],
                    ]),
                ]],
                ['username.any_of' => 'Username invalid']
            )->validate();
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            $this->assertSame('Username invalid', $exception->getMessage());
            $this->assertSame(['username' => ['Username invalid']], $exception->errors());
        }
    }
}
