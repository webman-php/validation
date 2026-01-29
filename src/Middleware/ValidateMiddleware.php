<?php
declare(strict_types=1);

namespace Webman\Validation\Middleware;

use InvalidArgumentException;
use ReflectionMethod;
use ReflectionParameter;
use Webman\Http\Request;
use Webman\Validation\Param;
use Webman\Validation\Validate;
use Webman\Validation\ValidationSetInterface;
use Webman\Validation\Validator;

final class ValidateMiddleware
{
    public function process(Request $request, callable $handler)
    {
        $controller = $request->controller ?: '';
        $action = $request->action ?: '';
        if ($controller === '' || $action === '' || !class_exists($controller)) {
            return $handler($request);
        }

        if (!method_exists($controller, $action)) {
            return $handler($request);
        }

        $method = new ReflectionMethod($controller, $action);

        $data = $this->getRequestData($request);

        $this->handleMethodValidation($method, $data);
        $this->handleParamValidation($method, $data);

        return $handler($request);
    }

    private function handleMethodValidation(ReflectionMethod $method, array $data): void
    {
        $attributes = $method->getAttributes(Validate::class, \ReflectionAttribute::IS_INSTANCEOF);
        if (!$attributes) {
            return;
        }

        foreach ($attributes as $attribute) {
            /** @var Validate $config */
            $config = $attribute->newInstance();
            [$rules, $messages, $attributesMap] = $this->resolveMethodRules($config);
            if (!$rules) {
                continue;
            }
            Validator::make($data, $rules, $messages, $attributesMap)->validate();
        }
    }

    private function handleParamValidation(ReflectionMethod $method, array $data): void
    {
        foreach ($method->getParameters() as $parameter) {
            $attributes = $parameter->getAttributes(Param::class, \ReflectionAttribute::IS_INSTANCEOF);
            if (!$attributes) {
                continue;
            }
            foreach ($attributes as $attribute) {
                /** @var Param $config */
                $config = $attribute->newInstance();
                $this->validateSingleParam($parameter, $config, $data);
            }
        }
    }

    private function validateSingleParam(
        ReflectionParameter $parameter,
        Param $config,
        array $data
    ): void {
        $name = $parameter->getName();
        $value = $data[$name] ?? null;
        if ($value === null && $parameter->isDefaultValueAvailable()) {
            $value = $parameter->getDefaultValue();
        }

        $rules = $config->rules;

        $attributes = [];
        if ($config->attribute !== '') {
            $attributes = [$name => $config->attribute];
        }

        Validator::make(
            [$name => $value],
            [$name => $rules],
            $config->messages,
            $attributes
        )->validate();
    }

    private function resolveMethodRules(Validate $config): array
    {
        if ($config->validator !== null) {
            if ($config->rules) {
                throw new InvalidArgumentException('Validate cannot set both validator and rules.');
            }
            if (!class_exists($config->validator)) {
                throw new InvalidArgumentException("Validator class not found: {$config->validator}");
            }
            if (!is_subclass_of($config->validator, ValidationSetInterface::class)) {
                throw new InvalidArgumentException("Validator must implement ValidationSetInterface: {$config->validator}");
            }
            $rules = $config->validator::rules($config->scene);
            $messages = $config->validator::messages($config->scene);
            $attributes = $config->validator::attributes($config->scene);
            return [$rules, $messages, $attributes];
        }

        return [$config->rules, $config->messages, $config->attributes];
    }

    private function getRequestData(Request $request): array
    {
        $routeParams = $request->route ? $request->route->param() : [];
        if (!is_array($routeParams)) {
            $routeParams = [];
        }
        return array_merge($request->all() ?: [], $routeParams);
    }
}
