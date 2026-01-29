<?php
declare(strict_types=1);

namespace Webman\Validation\Tests;

use PHPUnit\Framework\TestCase;
use Webman\Http\Request;
use Webman\Route\Route;
use support\validation\ValidationException;
use Webman\Validation\Middleware\ValidateMiddleware;
use support\validation\Param;
use support\validation\Validate;
use support\validation\ValidationSetInterface;

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
        $middleware = new ValidateMiddleware();
        $middleware->process($request, function () use (&$called) {
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

    public function testMethodValidateValidatorScene(): void
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

final class MethodSceneRules implements ValidationSetInterface
{
    public static function rules(string $scene = 'default'): array
    {
        return [
            'name' => 'required|string|min:2',
            'email' => 'required|email',
        ];
    }

    public static function messages(string $scene = 'default'): array
    {
        return [];
    }

    public static function attributes(string $scene = 'default'): array
    {
        return [];
    }
}

final class MethodSceneController
{
    #[Validate(validator: MethodSceneRules::class, scene: 'create')]
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
