<?php
declare(strict_types=1);

namespace Webman\Validation\Middleware;

use InvalidArgumentException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use Webman\Http\Request;
use Webman\Validation\Param;
use Webman\Validation\Validate;
use Webman\Validation\Validator;

final class ValidateMiddleware
{
    private static array $metadataCache = [];

    public function process(Request $request, callable $handler)
    {
        $controller = $request->controller ?: '';
        $action = $request->action ?: '';
        if ($controller === '' || $action === '' || !class_exists($controller)) {
            return $handler($request);
        }

        $metadata = $this->getMethodMetadata($controller, $action);
        if ($metadata === null || !$metadata['has']) {
            return $handler($request);
        }

        $data = $this->getRequestData($request);

        $this->handleMethodValidation($metadata['methods'], $data);
        $this->handleParamValidation($metadata['params'], $data);

        return $handler($request);
    }

    private function handleMethodValidation(array $methods, array $data): void
    {
        if ($methods === []) {
            return;
        }

        foreach ($methods as $config) {
            $this->validateMethod($config, $data);
        }
    }

    private function handleParamValidation(array $params, array $data): void
    {
        if ($params === []) {
            return;
        }

        $allData = [];
        $allRules = [];
        $allMessages = [];
        $allAttributes = [];

        foreach ($params as $item) {
            $name = $item['name'];
            /** @var Param $config */
            $config = $item['config'];

            $value = $data[$name] ?? null;
            if ($value === null && $item['hasDefault']) {
                $value = $item['default'];
            }

            $allData[$name] = $value;
            $allRules[$name] = $config->rules;

            // 处理 messages，确保 key 带有字段前缀，避免冲突
            foreach ($config->messages as $key => $message) {
                if (!str_contains($key, '.')) {
                    // 没有点号的 key 自动添加字段名前缀
                    $key = $name . '.' . $key;
                }
                $allMessages[$key] = $message;
            }

            if ($config->attribute !== '') {
                $allAttributes[$name] = $config->attribute;
            }
        }

        Validator::make($allData, $allRules, $allMessages, $allAttributes)->validate();
    }

    private function validateMethod(Validate $config, array $data): void
    {
        if ($config->validator !== null) {
            if ($config->rules !== []) {
                throw new InvalidArgumentException('Validate cannot set both validator and rules.');
            }
            if (!class_exists($config->validator)) {
                throw new InvalidArgumentException("Validator class not found: {$config->validator}");
            }
            if (!is_subclass_of($config->validator, \Webman\Validation\Validator::class)) {
                throw new InvalidArgumentException("Validator must extend Webman\\Validation\\Validator (or support\\validation\\Validator): {$config->validator}");
            }

            $validator = $config->validator::make($data);
            if ($config->scene !== null) {
                $validator = $validator->withScene($config->scene);
            }
            $validator->validate();
            return;
        }

        if ($config->rules === []) {
            return;
        }

        Validator::make($data, $config->rules, $config->messages, $config->attributes)->validate();
    }

    private function getRequestData(Request $request): array
    {
        $routeParams = $request->route ? $request->route->param() : [];
        if (!is_array($routeParams)) {
            $routeParams = [];
        }
        return array_merge($request->all() ?: [], $routeParams);
    }

    private function getMethodMetadata(string $controller, string $action): ?array
    {
        $key = $controller . '::' . $action;
        if (isset(self::$metadataCache[$key])) {
            return self::$metadataCache[$key];
        }

        if (!method_exists($controller, $action)) {
            return self::$metadataCache[$key] = null;
        }

        $method = new ReflectionMethod($controller, $action);

        $methods = [];
        $hasValidateAttribute = false;
        foreach ($method->getAttributes(Validate::class, \ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            /** @var Validate $config */
            $config = $attribute->newInstance();
            $methods[] = $config;
            $hasValidateAttribute = true;
        }

        $parameters = $method->getParameters();
        $hasAnyParamAttribute = false;
        foreach ($parameters as $parameter) {
            if ($parameter->getAttributes(Param::class, \ReflectionAttribute::IS_INSTANCEOF) !== []) {
                $hasAnyParamAttribute = true;
                break;
            }
        }

        $inferWhenAnnotationsPresent = $hasValidateAttribute || $hasAnyParamAttribute;

        $params = [];
        foreach ($parameters as $parameter) {
            $paramConfig = $this->resolveParamConfig($parameter, $inferWhenAnnotationsPresent);
            if ($paramConfig === null) {
                continue;
            }
            $hasDefault = $parameter->isDefaultValueAvailable();
            $params[] = [
                'name' => $parameter->getName(),
                'config' => $paramConfig,
                'hasDefault' => $hasDefault,
                'default' => $hasDefault ? $parameter->getDefaultValue() : null,
            ];
        }

        $metadata = [
            'has' => $methods !== [] || $params !== [],
            'methods' => $methods,
            'params' => $params,
        ];

        return self::$metadataCache[$key] = $metadata;
    }

    private function resolveParamConfig(ReflectionParameter $parameter, bool $inferWhenAnnotationsPresent): ?Param
    {
        $attributes = $parameter->getAttributes(Param::class, \ReflectionAttribute::IS_INSTANCEOF);
        if ($attributes !== []) {
            /** @var Param $config */
            $config = $attributes[0]->newInstance();

            // Auto-complete rules based on parameter signature.
            $completedRules = $this->completeRulesFromParameter($parameter, $config->rules);
            if ($completedRules !== $config->rules) {
                return new Param(
                    rules: $completedRules,
                    messages: $config->messages,
                    attribute: $config->attribute
                );
            }

            return $config;
        }

        if (!$inferWhenAnnotationsPresent) {
            return null;
        }

        if ($this->shouldSkipParameter($parameter)) {
            return null;
        }

        $rules = $this->inferRulesFromParameter($parameter);
        return new Param(rules: $rules);
    }

    private function shouldSkipParameter(ReflectionParameter $parameter): bool
    {
        $type = $parameter->getType();
        if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
            return false;
        }

        $name = $type->getName();
        if ($name === '') {
            return true;
        }

        // Skip framework request injection.
        if (is_a($name, Request::class, true)) {
            return true;
        }

        // Skip other class-typed parameters by default (services/DTOs/etc).
        return true;
    }

    private function inferRulesFromParameter(ReflectionParameter $parameter): string|array
    {
        $rules = [];

        $type = $parameter->getType();
        $isNullable = $type instanceof ReflectionNamedType && $type->allowsNull();

        // Required when: no default value AND not nullable.
        if (!$parameter->isDefaultValueAvailable() && !$isNullable) {
            $rules[] = 'required';
        }

        if ($type instanceof ReflectionUnionType) {
            // Union types are not inferred by default (developer can explicitly use #[Param]).
            return implode('|', $rules);
        }

        if ($type instanceof ReflectionNamedType && $type->isBuiltin()) {
            $mapped = $this->mapBuiltinTypeToRule($type->getName());
            if ($mapped !== '') {
                $rules[] = $mapped;
            }
            if ($isNullable) {
                $rules[] = 'nullable';
            }
        }

        return implode('|', $rules);
    }

    private function completeRulesFromParameter(ReflectionParameter $parameter, string|array $existingRules): string|array
    {
        $rulesString = is_array($existingRules) ? implode('|', $existingRules) : $existingRules;
        $rulesList = $rulesString !== '' ? explode('|', $rulesString) : [];

        $type = $parameter->getType();
        $isNullable = $type instanceof ReflectionNamedType && $type->allowsNull();

        // Auto-complete 'required' if: no default value, not nullable, and not already present.
        if (!$parameter->isDefaultValueAvailable() && !$isNullable && !$this->hasRule($rulesList, 'required')) {
            array_unshift($rulesList, 'required');
        }

        // Auto-complete type rule if: has builtin type and no type rule present.
        if ($type instanceof ReflectionNamedType && $type->isBuiltin()) {
            $mappedRule = $this->mapBuiltinTypeToRule($type->getName());
            if ($mappedRule !== '' && !$this->hasRule($rulesList, $mappedRule)) {
                // Insert type rule after 'required' if present, otherwise at the beginning.
                $requiredIndex = array_search('required', $rulesList, true);
                if ($requiredIndex !== false) {
                    array_splice($rulesList, $requiredIndex + 1, 0, $mappedRule);
                } else {
                    array_unshift($rulesList, $mappedRule);
                }
            }

            // Auto-complete 'nullable' if: type is nullable and not already present.
            if ($isNullable && !$this->hasRule($rulesList, 'nullable')) {
                $rulesList[] = 'nullable';
            }
        }

        $completedRules = implode('|', $rulesList);
        return is_array($existingRules) ? $rulesList : $completedRules;
    }

    private function hasRule(array $rules, string $ruleName): bool
    {
        foreach ($rules as $rule) {
            // Handle rules with parameters like 'min:1', 'in:a,b,c'.
            $name = explode(':', $rule, 2)[0];
            if ($name === $ruleName) {
                return true;
            }
        }
        return false;
    }

    private function mapBuiltinTypeToRule(string $type): string
    {
        return match ($type) {
            'string' => 'string',
            'int' => 'integer',
            'float' => 'numeric',
            'bool' => 'boolean',
            'array' => 'array',
            default => '',
        };
    }
}
