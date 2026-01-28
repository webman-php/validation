<?php
declare(strict_types=1);

namespace Webman\Validation\Tests;

use PHPUnit\Framework\TestCase;
use support\validation\Validator;
use support\validation\ValidationException;

final class ValidationResultTest extends TestCase
{
    public function testValidatePass(): void
    {
        $result = Validator::make(
            ['email' => 'user@example.com'],
            ['email' => 'required|email']
        );

        $validated = $result->validate();
        $this->assertSame(['email' => 'user@example.com'], $validated);
    }

    public function testValidateFailThrowsException(): void
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
            $this->assertSame(['email' => ['Email invalid']], $exception->errors());
            $this->assertSame('Email invalid', $exception->first());
        }
    }
}
