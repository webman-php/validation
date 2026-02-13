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

    public function testParamValidationComplexSignatureDefaultValueWithRequiredShouldPassWhenMissing(): void
    {
        $request = $this->makeRequest(
            controller: ParamComplexController::class,
            action: 'test',
            query: ['from' => 'api', 'id' => 1, 'price' => 12.34],
            routeParams: ['data' => ['k' => 'v']]
        );

        $called = false;
        (new ValidateMiddleware())->process($request, function () use (&$called) {
            $called = true;
            return 'ok';
        });

        $this->assertTrue($called);
    }

    public function testParamValidationComplexSignatureMissingFromShouldFail(): void
    {
        $request = $this->makeRequest(
            controller: ParamComplexController::class,
            action: 'test',
            query: ['id' => 1, 'price' => 12.34],
            routeParams: ['data' => ['k' => 'v']]
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('From required');
        (new ValidateMiddleware())->process($request, fn () => 'ok');
    }

    public function testParamValidationComplexSignatureMissingIdShouldFail(): void
    {
        $request = $this->makeRequest(
            controller: ParamComplexController::class,
            action: 'test',
            query: ['from' => 'api', 'price' => 12.34],
            routeParams: ['data' => ['k' => 'v']]
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Id required');
        (new ValidateMiddleware())->process($request, fn () => 'ok');
    }

    public function testParamValidationComplexSignatureMissingPriceShouldFail(): void
    {
        $request = $this->makeRequest(
            controller: ParamComplexController::class,
            action: 'test',
            query: ['from' => 'api', 'id' => 1],
            routeParams: ['data' => ['k' => 'v']]
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Price required');
        (new ValidateMiddleware())->process($request, fn () => 'ok');
    }

    public function testParamValidationComplexSignatureMissingDataShouldFail(): void
    {
        $request = $this->makeRequest(
            controller: ParamComplexController::class,
            action: 'test',
            query: ['from' => 'api', 'id' => 1, 'price' => 12.34]
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Data required');
        (new ValidateMiddleware())->process($request, fn () => 'ok');
    }

    public function testAutoInferValidationMissingRequiredParamsShouldFail(): void
    {
        $request = $this->makeRequest(
            controller: AutoInferController::class,
            action: 'send'
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The name field is required.');
        (new ValidateMiddleware())->process($request, fn () => 'ok');
    }

    public function testAutoInferValidationWrongTypeShouldFail(): void
    {
        $request = $this->makeRequest(
            controller: AutoInferController::class,
            action: 'send',
            query: ['name' => 'Tom', 'age' => 'bad']
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The age field must be an integer.');
        (new ValidateMiddleware())->process($request, fn () => 'ok');
    }

    public function testAutoInferValidationDefaultValueParamNotRequiredShouldPass(): void
    {
        $request = $this->makeRequest(
            controller: AutoInferController::class,
            action: 'send',
            query: ['name' => 'Tom', 'age' => 18]
        );

        $called = false;
        (new ValidateMiddleware())->process($request, function () use (&$called) {
            $called = true;
            return 'ok';
        });

        $this->assertTrue($called);
    }

    public function testAutoInferWithParamButWithoutValidateMissingShouldFail(): void
    {
        $request = $this->makeRequest(
            controller: ParamOnlyInferController::class,
            action: 'send',
            query: ['name' => 'Tom']
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The age field is required.');
        (new ValidateMiddleware())->process($request, fn () => 'ok');
    }

    public function testAutoInferWithParamButWithoutValidateWrongTypeShouldFail(): void
    {
        $request = $this->makeRequest(
            controller: ParamOnlyInferController::class,
            action: 'send',
            query: ['name' => 'Tom', 'age' => 'bad']
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The age field must be an integer.');
        (new ValidateMiddleware())->process($request, fn () => 'ok');
    }

    public function testAutoInferWithParamButWithoutValidatePass(): void
    {
        $request = $this->makeRequest(
            controller: ParamOnlyInferController::class,
            action: 'send',
            query: ['name' => 'Tom', 'age' => 18]
        );

        $called = false;
        (new ValidateMiddleware())->process($request, function () use (&$called) {
            $called = true;
            return 'ok';
        });

        $this->assertTrue($called);
    }

    public function testParamIncompleteRulesAutoCompleteRequiredShouldFail(): void
    {
        $request = $this->makeRequest(
            controller: ParamIncompleteRulesController::class,
            action: 'send',
            query: ['name' => 'Tom']
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The age field is required.');
        (new ValidateMiddleware())->process($request, fn () => 'ok');
    }

    public function testParamIncompleteRulesAutoCompleteTypeShouldFail(): void
    {
        $request = $this->makeRequest(
            controller: ParamIncompleteRulesController::class,
            action: 'send',
            query: ['name' => 'Tom', 'age' => 'bad']
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The age field must be an integer.');
        (new ValidateMiddleware())->process($request, fn () => 'ok');
    }

    public function testParamIncompleteRulesAutoCompletePass(): void
    {
        $request = $this->makeRequest(
            controller: ParamIncompleteRulesController::class,
            action: 'send',
            query: ['name' => 'Tom', 'age' => 5]
        );

        $called = false;
        (new ValidateMiddleware())->process($request, function () use (&$called) {
            $called = true;
            return 'ok';
        });

        $this->assertTrue($called);
    }

    public function testNullableTypeNotRequiredShouldPassWhenMissing(): void
    {
        $request = $this->makeRequest(
            controller: NullableParamController::class,
            action: 'send',
            query: ['name' => 'Tom']
        );

        $called = false;
        (new ValidateMiddleware())->process($request, function () use (&$called) {
            $called = true;
            return 'ok';
        });

        $this->assertTrue($called);
    }

    public function testNullableTypeWrongTypeShouldFail(): void
    {
        $request = $this->makeRequest(
            controller: NullableParamController::class,
            action: 'send',
            query: ['name' => 'Tom', 'age' => 'bad']
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The age field must be an integer.');
        (new ValidateMiddleware())->process($request, fn () => 'ok');
    }

    public function testNullableTypeWithValueShouldPass(): void
    {
        $request = $this->makeRequest(
            controller: NullableParamController::class,
            action: 'send',
            query: ['name' => 'Tom', 'age' => 18]
        );

        $called = false;
        (new ValidateMiddleware())->process($request, function () use (&$called) {
            $called = true;
            return 'ok';
        });

        $this->assertTrue($called);
    }

    public function testParamEmptyRulesAutoInferAllShouldFail(): void
    {
        $request = $this->makeRequest(
            controller: ParamEmptyRulesController::class,
            action: 'send',
            query: []
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('ID is required');
        (new ValidateMiddleware())->process($request, fn () => 'ok');
    }

    public function testParamEmptyRulesAutoInferAllTypeShouldFail(): void
    {
        $request = $this->makeRequest(
            controller: ParamEmptyRulesController::class,
            action: 'send',
            query: ['id' => 'bad']
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The id field must be an integer.');
        (new ValidateMiddleware())->process($request, fn () => 'ok');
    }

    public function testParamEmptyRulesAutoInferAllPass(): void
    {
        $request = $this->makeRequest(
            controller: ParamEmptyRulesController::class,
            action: 'send',
            query: ['id' => 123]
        );

        $called = false;
        (new ValidateMiddleware())->process($request, function () use (&$called) {
            $called = true;
            return 'ok';
        });

        $this->assertTrue($called);
    }

    public function testParamHasRequiredOnlyAutoCompleteType(): void
    {
        $request = $this->makeRequest(
            controller: ParamHasRequiredOnlyController::class,
            action: 'send',
            query: ['id' => 'bad']
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The id field must be an integer.');
        (new ValidateMiddleware())->process($request, fn () => 'ok');
    }

    public function testParamHasTypeOnlyAutoCompleteRequired(): void
    {
        $request = $this->makeRequest(
            controller: ParamHasTypeOnlyController::class,
            action: 'send',
            query: []
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The id field is required.');
        (new ValidateMiddleware())->process($request, fn () => 'ok');
    }

    public function testParamWithDefaultValueNotAutoCompleteRequired(): void
    {
        $request = $this->makeRequest(
            controller: ParamWithDefaultValueController::class,
            action: 'send',
            query: []
        );

        $called = false;
        (new ValidateMiddleware())->process($request, function () use (&$called) {
            $called = true;
            return 'ok';
        });

        $this->assertTrue($called);
    }

    public function testParamWithDefaultValueAutoCompleteTypeOnly(): void
    {
        $request = $this->makeRequest(
            controller: ParamWithDefaultValueController::class,
            action: 'send',
            query: ['age' => 'bad']
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The age field must be an integer.');
        (new ValidateMiddleware())->process($request, fn () => 'ok');
    }

    public function testNullableParamWithRulesAutoCompleteTypeAndNullable(): void
    {
        $request = $this->makeRequest(
            controller: NullableParamWithRulesController::class,
            action: 'send',
            query: []
        );

        $called = false;
        (new ValidateMiddleware())->process($request, function () use (&$called) {
            $called = true;
            return 'ok';
        });

        $this->assertTrue($called);
    }

    public function testNullableParamWithRulesTypeShouldFail(): void
    {
        $request = $this->makeRequest(
            controller: NullableParamWithRulesController::class,
            action: 'send',
            query: ['age' => 'bad']
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The age field must be an integer.');
        (new ValidateMiddleware())->process($request, fn () => 'ok');
    }

    public function testAutoInferFloatType(): void
    {
        $request = $this->makeRequest(
            controller: TypeInferController::class,
            action: 'send',
            query: ['name' => 'Tom', 'price' => 'bad', 'active' => true, 'tags' => ['a']]
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The price field must be a number.');
        (new ValidateMiddleware())->process($request, fn () => 'ok');
    }

    public function testAutoInferBoolType(): void
    {
        $request = $this->makeRequest(
            controller: TypeInferController::class,
            action: 'send',
            query: ['name' => 'Tom', 'price' => 12.5, 'active' => 'bad', 'tags' => ['a']]
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The active field must be true or false.');
        (new ValidateMiddleware())->process($request, fn () => 'ok');
    }

    public function testAutoInferArrayType(): void
    {
        $request = $this->makeRequest(
            controller: TypeInferController::class,
            action: 'send',
            query: ['name' => 'Tom', 'price' => 12.5, 'active' => true, 'tags' => 'bad']
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The tags field must be an array.');
        (new ValidateMiddleware())->process($request, fn () => 'ok');
    }

    public function testAutoInferAllTypesPass(): void
    {
        $request = $this->makeRequest(
            controller: TypeInferController::class,
            action: 'send',
            query: ['name' => 'Tom', 'price' => 12.5, 'active' => true, 'tags' => ['a', 'b']]
        );

        $called = false;
        (new ValidateMiddleware())->process($request, function () use (&$called) {
            $called = true;
            return 'ok';
        });

        $this->assertTrue($called);
    }

    public function testNoAnnotationMethodShouldNotValidate(): void
    {
        $request = $this->makeRequest(
            controller: NoAnnotationController::class,
            action: 'send',
            query: []
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

    public function testValidatorClassWithAutoInferParamsMissingShouldFail(): void
    {
        $request = $this->makeRequest(
            controller: ValidatorWithAutoInferParamsController::class,
            action: 'send',
            query: ['name' => 'Tom', 'email' => 'user@example.com']
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The title field is required.');
        (new ValidateMiddleware())->process($request, fn () => 'ok');
    }

    public function testValidatorClassWithAutoInferParamsTypeShouldFail(): void
    {
        $request = $this->makeRequest(
            controller: ValidatorWithAutoInferParamsController::class,
            action: 'send',
            query: ['name' => 'Tom', 'email' => 'user@example.com', 'title' => 'Hello', 'count' => 'bad']
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The count field must be an integer.');
        (new ValidateMiddleware())->process($request, fn () => 'ok');
    }

    public function testValidatorClassWithAutoInferParamsPass(): void
    {
        $request = $this->makeRequest(
            controller: ValidatorWithAutoInferParamsController::class,
            action: 'send',
            query: ['name' => 'Tom', 'email' => 'user@example.com', 'title' => 'Hello', 'count' => 5]
        );

        $called = false;
        (new ValidateMiddleware())->process($request, function () use (&$called) {
            $called = true;
            return 'ok';
        });

        $this->assertTrue($called);
    }

    public function testValidatorClassValidationFailButAutoInferParamsOk(): void
    {
        $request = $this->makeRequest(
            controller: ValidatorWithAutoInferParamsController::class,
            action: 'send',
            query: ['name' => 'T', 'email' => 'bad-email', 'title' => 'Hello', 'count' => 5]
        );

        $this->expectException(ValidationException::class);
        (new ValidateMiddleware())->process($request, fn () => 'ok');
    }

    // ───── Closure / Function route tests ─────

    public function testClosureParamValidationPass(): void
    {
        $closure = function (
            Request $request,
            #[Param(rules: 'required|integer')]
            int $id
        ): void {};

        $request = $this->makeCallableRequest($closure, '/closure-pass', query: ['id' => 5]);

        $called = false;
        (new ValidateMiddleware())->process($request, function () use (&$called) {
            $called = true;
            return 'ok';
        });

        $this->assertTrue($called);
    }

    public function testClosureParamValidationFail(): void
    {
        $closure = function (
            Request $request,
            #[Param(rules: 'required|integer', messages: ['id.integer' => 'Id must be integer'])]
            int $id
        ): void {};

        $request = $this->makeCallableRequest($closure, '/closure-fail', query: ['id' => 'bad']);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Id must be integer');
        (new ValidateMiddleware())->process($request, fn () => 'ok');
    }

    public function testClosureAutoInferFromParamAnnotation(): void
    {
        $closure = function (
            Request $request,
            #[Param(rules: 'string')]
            string $name,
            int $age
        ): void {};

        $request = $this->makeCallableRequest($closure, '/closure-infer', query: ['name' => 'Tom', 'age' => 'bad']);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The age field must be an integer.');
        (new ValidateMiddleware())->process($request, fn () => 'ok');
    }

    public function testClosureAutoInferFromParamAnnotationPass(): void
    {
        $closure = function (
            Request $request,
            #[Param(rules: 'string')]
            string $name,
            int $age
        ): void {};

        $request = $this->makeCallableRequest($closure, '/closure-infer-pass', query: ['name' => 'Tom', 'age' => 18]);

        $called = false;
        (new ValidateMiddleware())->process($request, function () use (&$called) {
            $called = true;
            return 'ok';
        });

        $this->assertTrue($called);
    }

    public function testClosureWithoutAnnotationsSkipsValidation(): void
    {
        $closure = function (Request $request, string $name, int $age): void {};

        $request = $this->makeCallableRequest($closure, '/closure-no-annotations', query: []);

        $called = false;
        (new ValidateMiddleware())->process($request, function () use (&$called) {
            $called = true;
            return 'ok';
        });

        $this->assertTrue($called);
    }

    public function testClosureRouteParamsValidation(): void
    {
        $closure = function (
            Request $request,
            #[Param(rules: 'required|integer')]
            int $id
        ): void {};

        $request = $this->makeCallableRequest($closure, '/closure-route-params', routeParams: ['id' => 7]);

        $called = false;
        (new ValidateMiddleware())->process($request, function () use (&$called) {
            $called = true;
            return 'ok';
        });

        $this->assertTrue($called);
    }

    public function testClosureMultipleParamsValidation(): void
    {
        $closure = function (
            Request $request,
            #[Param(rules: 'required|string')]
            string $name,
            #[Param(rules: 'required|integer')]
            int $age
        ): void {};

        $request = $this->makeCallableRequest($closure, '/closure-multi-pass', query: ['name' => 'Tom', 'age' => 18]);

        $called = false;
        (new ValidateMiddleware())->process($request, function () use (&$called) {
            $called = true;
            return 'ok';
        });

        $this->assertTrue($called);
    }

    public function testClosureMultipleParamsValidationFail(): void
    {
        $closure = function (
            Request $request,
            #[Param(rules: 'required|string')]
            string $name,
            #[Param(rules: 'required|integer')]
            int $age
        ): void {};

        $request = $this->makeCallableRequest($closure, '/closure-multi-fail', query: ['name' => 'Tom']);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The age field is required.');
        (new ValidateMiddleware())->process($request, fn () => 'ok');
    }

    public function testNoRouteSkipsValidation(): void
    {
        $buffer = "GET /test HTTP/1.1\r\nHost: localhost\r\n\r\n";
        $request = new Request($buffer);
        $request->controller = '';
        $request->action = '';
        $request->route = null;

        $called = false;
        (new ValidateMiddleware())->process($request, function () use (&$called) {
            $called = true;
            return 'ok';
        });

        $this->assertTrue($called);
    }

    // ───── Helpers ─────

    private function makeCallableRequest(
        callable $callback,
        string $path = '/callable-test',
        array $query = [],
        array $routeParams = []
    ): Request {
        $queryString = $query ? http_build_query($query) : '';
        $fullPath = $path . ($queryString !== '' ? '?' . $queryString : '');
        $buffer = "GET {$fullPath} HTTP/1.1\r\nHost: localhost\r\n\r\n";

        $request = new Request($buffer);
        $request->controller = '';
        $request->action = '';
        $request->route = new Route('GET', $path, $callback);
        $request->route->setParams($routeParams);
        return $request;
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

final class ParamComplexController
{
    public function test(
        #[Param(rules: 'required|string', messages: ['required' => 'From required'])]
        string $from,
        #[Param(rules: 'required|integer', messages: ['required' => 'Id required'])]
        int $id,
        #[Param(rules: 'required|numeric', messages: ['required' => 'Price required'])]
        float $price,
        #[Param(rules: 'required|array', messages: ['required' => 'Data required'])]
        array $data,
        #[Param(rules: 'required|string', messages: ['required' => 'Content required'])]
        string $content = '默认值'
    ): void {
    }
}

final class AutoInferController
{
    #[Validate]
    public function send(
        \Webman\Http\Request $request,
        string $name,
        int $age,
        $sex = 'male'
    ): void {
    }
}

final class ParamOnlyInferController
{
    public function send(
        #[Param(rules: 'string')]
        string $name,
        int $age
    ): void {
    }
}

final class ParamIncompleteRulesController
{
    #[Validate]
    public function send(
        #[Param(rules: 'string')]
        string $name,
        #[Param(rules: 'min:1')]
        int $age
    ): void {
    }
}

final class NullableParamController
{
    #[Validate]
    public function send(
        string $name,
        ?int $age
    ): void {
    }
}

final class ParamEmptyRulesController
{
    #[Validate]
    public function send(
        #[Param(messages: ['id.required' => 'ID is required'])]
        int $id
    ): void {
    }
}

final class ParamHasRequiredOnlyController
{
    #[Validate]
    public function send(
        #[Param(rules: 'required')]
        int $id
    ): void {
    }
}

final class ParamHasTypeOnlyController
{
    #[Validate]
    public function send(
        #[Param(rules: 'integer')]
        int $id
    ): void {
    }
}

final class ParamWithDefaultValueController
{
    #[Validate]
    public function send(
        #[Param(rules: 'min:1')]
        int $age = 10
    ): void {
    }
}

final class NullableParamWithRulesController
{
    #[Validate]
    public function send(
        #[Param(rules: 'min:1')]
        ?int $age
    ): void {
    }
}

final class TypeInferController
{
    #[Validate]
    public function send(
        string $name,
        float $price,
        bool $active,
        array $tags
    ): void {
    }
}

final class NoAnnotationController
{
    public function send(
        string $name,
        int $age
    ): void {
    }
}

final class ValidatorWithAutoInferParamsController
{
    #[Validate(validator: MethodSceneValidator::class)]
    public function send(
        Request $request,
        string $title,
        int $count
    ): void {
    }
}

final class CustomValidationException extends BaseValidationException
{
}
