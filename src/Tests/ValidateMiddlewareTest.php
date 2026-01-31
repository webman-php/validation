<?php
declare(strict_types=1);

namespace Webman\Validation\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Webman\Http\Request;
use Webman\Route\Route;
use Webman\Validation\Exceptions\ValidationException as BaseValidationException;
use Webman\Validation\Middleware\ValidateMiddleware;
use support\validation\Param;
use support\validation\Validate;
use support\validation\ValidationException;
use support\validation\Validator;

final class ValidateMiddlewareTest extends TestCase
{
    public function testMethodValidateRulesPass(): void
    {
        $request = $this->makeRequest(
            controller: MethodRulesController::class,
            action: 'send',
            query: ['email' => 'user@example.com']
        );

        $called = false;
        (new ValidateMiddleware())->process($request, function () use (&$called) {
            $called = true;
            return 'ok';
        });

        $this->assertTrue($called);
    }

    public function testMethodValidateRulesFail(): void
    {
        $request = $this->makeRequest(
            controller: MethodRulesController::class,
            action: 'send',
            query: ['email' => 'bad-email']
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Email invalid');
        (new ValidateMiddleware())->process($request, fn () => 'ok');
    }

    public function testMethodValidateValidatorWithScenePass(): void
    {
        $request = $this->makeRequest(
            controller: MethodSceneController::class,
            action: 'send',
            query: ['name' => 'Tom', 'email' => 'user@example.com']
        );

        $called = false;
        (new ValidateMiddleware())->process($request, function () use (&$called) {
            $called = true;
            return 'ok';
        });

        $this->assertTrue($called);
    }

    public function testMethodValidateValidatorWithoutScenePass(): void
    {
        $request = $this->makeRequest(
            controller: MethodValidatorNoSceneController::class,
            action: 'send',
            query: ['name' => 'Tom', 'email' => 'user@example.com']
        );

        $called = false;
        (new ValidateMiddleware())->process($request, function () use (&$called) {
            $called = true;
            return 'ok';
        });

        $this->assertTrue($called);
    }

    public function testMethodValidateValidatorSceneNotDefinedThrows(): void
    {
        $request = $this->makeRequest(
            controller: MethodSceneNotDefinedController::class,
            action: 'send',
            query: ['name' => 'Tom', 'email' => 'user@example.com']
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Validation scene not defined: missing');
        (new ValidateMiddleware())->process($request, fn () => 'ok');
    }

    public function testValidateAttributeCannotSetBothValidatorAndRules(): void
    {
        $request = $this->makeRequest(
            controller: MethodValidatorAndRulesController::class,
            action: 'send',
            query: ['email' => 'user@example.com']
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Validate cannot set both validator and rules.');
        (new ValidateMiddleware())->process($request, fn () => 'ok');
    }

    public function testMultipleMethodValidation(): void
    {
        $request = $this->makeRequest(
            controller: MethodMultipleController::class,
            action: 'send',
            query: ['email' => 'user@example.com', 'token' => 'abc']
        );

        $called = false;
        (new ValidateMiddleware())->process($request, function () use (&$called) {
            $called = true;
            return 'ok';
        });

        $this->assertTrue($called);
    }

    public function testParamValidationUsesRouteParams(): void
    {
        $request = $this->makeRequest(
            controller: ParamController::class,
            action: 'send',
            routeParams: ['id' => 7]
        );

        $called = false;
        (new ValidateMiddleware())->process($request, function () use (&$called) {
            $called = true;
            return 'ok';
        });

        $this->assertTrue($called);
    }

    public function testParamValidationFail(): void
    {
        $request = $this->makeRequest(
            controller: ParamController::class,
            action: 'send',
            routeParams: ['id' => 'not-int']
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Id must be integer');
        (new ValidateMiddleware())->process($request, fn () => 'ok');
    }

    public function testParamValidationUsesDefaultValueWhenMissing(): void
    {
        $request = $this->makeRequest(
            controller: ParamDefaultController::class,
            action: 'send'
        );

        $called = false;
        (new ValidateMiddleware())->process($request, function () use (&$called) {
            $called = true;
            return 'ok';
        });

        $this->assertTrue($called);
    }

    public function testParamValidationCustomAttributesMessage(): void
    {
        $request = $this->makeRequest(
            controller: ParamMessageController::class,
            action: 'send',
            query: ['email' => 'bad-email']
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Email Address is invalid');
        (new ValidateMiddleware())->process($request, fn () => 'ok');
    }

    public function testValidationUsesConfiguredExceptionClass(): void
    {
        validation_test_set_config([
            'plugin' => [
                'webman' => [
                    'validation' => [
                        'app' => [
                            'exception' => CustomValidationException::class,
                        ],
                    ],
                ],
            ],
        ]);

        try {
            $request = $this->makeRequest(
                controller: MethodRulesController::class,
                action: 'send',
                query: ['email' => 'bad-email']
            );

            $this->expectException(CustomValidationException::class);
            (new ValidateMiddleware())->process($request, fn () => 'ok');
        } finally {
            validation_test_set_config([
                'plugin' => [
                    'webman' => [
                        'validation' => [
                            'app' => [
                                'exception' => \support\validation\ValidationException::class,
                            ],
                        ],
                    ],
                ],
            ]);
        }
    }

    public function testMixedMethodAndParamValidation(): void
    {
        $request = $this->makeRequest(
            controller: MixedController::class,
            action: 'send',
            query: ['token' => 't', 'from' => 'user@example.com'],
            routeParams: ['id' => 3]
        );

        $called = false;
        (new ValidateMiddleware())->process($request, function () use (&$called) {
            $called = true;
            return 'ok';
        });

        $this->assertTrue($called);
    }

    private function makeRequest(
        string $controller,
        string $action,
        array $query = [],
        array $body = [],
        array $routeParams = []
    ): Request {
        $method = $body ? 'POST' : 'GET';
        $queryString = $query ? http_build_query($query) : '';
        $path = '/test' . ($queryString !== '' ? '?' . $queryString : '');
        $bodyString = $body ? http_build_query($body) : '';

        $headers = [
            "Host: localhost",
        ];
        if ($bodyString !== '') {
            $headers[] = "Content-Type: application/x-www-form-urlencoded";
            $headers[] = "Content-Length: " . strlen($bodyString);
        }
        $buffer = $method . ' ' . $path . " HTTP/1.1\r\n" . implode("\r\n", $headers) . "\r\n\r\n" . $bodyString;

        $request = new Request($buffer);
        $request->controller = $controller;
        $request->action = $action;
        $request->route = new Route($method, '/test', [$controller, $action]);
        $request->route->setParams($routeParams);
        return $request;
    }
}

final class MethodRulesController
{
    #[Validate(
        rules: ['email' => 'required|email'],
        messages: ['email.email' => 'Email invalid']
    )]
    public function send(Request $request): void
    {
    }
}

final class MethodSceneValidator extends Validator
{
    protected array $rules = [
        'name' => 'required|string|min:2',
        'email' => 'required|email',
    ];

    protected array $scenes = [
        'create' => ['name', 'email'],
    ];
}

final class MethodSceneController
{
    #[Validate(validator: MethodSceneValidator::class, scene: 'create')]
    public function send(Request $request): void
    {
    }
}

final class MethodValidatorNoSceneController
{
    #[Validate(validator: MethodSceneValidator::class)]
    public function send(Request $request): void
    {
    }
}

final class MethodSceneNotDefinedController
{
    #[Validate(validator: MethodSceneValidator::class, scene: 'missing')]
    public function send(Request $request): void
    {
    }
}

final class MethodValidatorAndRulesController
{
    #[Validate(validator: MethodSceneValidator::class, rules: ['email' => 'required|email'])]
    public function send(Request $request): void
    {
    }
}

final class MethodMultipleController
{
    #[Validate(rules: ['email' => 'required|email'])]
    #[Validate(rules: ['token' => 'required|string'])]
    public function send(Request $request): void
    {
    }
}

final class ParamController
{
    public function send(
        #[Param(
            rules: 'required|integer',
            messages: ['id.integer' => 'Id must be integer']
        )]
        int $id
    ): void {
    }
}

final class ParamDefaultController
{
    public function send(
        #[Param(rules: 'required|string')]
        string $token = 'default-token'
    ): void {
    }
}

final class ParamMessageController
{
    public function send(
        #[Param(
            rules: 'required|email',
            messages: ['email.email' => 'The :attribute is invalid'],
            attribute: 'Email Address'
        )]
        string $email
    ): void {
    }
}

final class MixedController
{
    #[Validate(rules: ['token' => 'required|string'])]
    public function send(
        Request $request,
        #[Param(rules: 'required|email')]
        string $from,
        #[Param(rules: 'required|integer')]
        int $id
    ): void {
    }
}

final class CustomValidationException extends BaseValidationException
{
}
