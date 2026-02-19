<?php
declare(strict_types=1);

namespace Webman\Validation;

use Closure;
use InvalidArgumentException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use Webman\Http\Request;
use Webman\Validation\Annotation\Param;
use Webman\Validation\Annotation\Validate;

final class Middleware
{
    private static array $metadataCache = [];

    public function process(Request $request, callable $handler)
    {
        $metadata = $this->resolveMetadata($request);
        if ($metadata === null || !$metadata['has']) {
            return $handler($request);
        }

        $data = $this->getRequestData($request);

        $this->handleMethodValidation($metadata['methods'], $data);
        $this->handleParamValidation($metadata['params'], $data);

        return $handler($request);
    }

    private function resolveMetadata(Request $request): ?array
    {
        $controller = $request->controller ?: '';
        $action = $request->action ?: '';

        if ($controller !== '' && $action !== '' && class_exists($controller)) {
            return $this->getMethodMetadata($controller, $action);
        }

        return $this->getCallableMetadata($request);
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
            /** @var \Webman\Validation\Annotation\Param $config */
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

        $reflection = new ReflectionMethod($controller, $action);

        return self::$metadataCache[$key] = $this->buildMetadataFromReflection($reflection, true);
    }

    /**
     * Resolve metadata for closure / named-function route handlers.
     */
    private function getCallableMetadata(Request $request): ?array
    {
        $route = $request->route;
        if (!$route || !method_exists($route, 'getCallback')) {
            return null;
        }

        $callback = $route->getCallback();

        // Only handle closures and named function strings.
        if (!$callback instanceof Closure && !is_string($callback)) {
            return null;
        }

        if (is_string($callback) && !function_exists($callback)) {
            return null;
        }

        $cacheKey = is_string($callback)
            ? 'func::' . $callback
            : 'callable::' . $route->getPath();

        if (isset(self::$metadataCache[$cacheKey])) {
            return self::$metadataCache[$cacheKey];
        }

        $reflection = new ReflectionFunction($callback);

        // Named functions support function-level #[Validate]; closures do not.
        $supportsMethodAttributes = is_string($callback);

        return self::$metadataCache[$cacheKey] = $this->buildMetadataFromReflection(
            $reflection,
            $supportsMethodAttributes
        );
    }

    /**
     * Build validation metadata from a ReflectionMethod or ReflectionFunction.
     */
    private function buildMetadataFromReflection(
        ReflectionFunctionAbstract $reflection,
        bool $supportsMethodAttributes
    ): array {
        $methods = [];
        if ($supportsMethodAttributes) {
            foreach ($reflection->getAttributes(Validate::class, \ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
                $methods[] = $attribute->newInstance();
            }
        }

        $parameters = $reflection->getParameters();
        $hasAnyParamAttribute = false;
        foreach ($parameters as $parameter) {
            if ($parameter->getAttributes(Param::class, \ReflectionAttribute::IS_INSTANCEOF) !== []) {
                $hasAnyParamAttribute = true;
                break;
            }
        }

        $inferWhenAnnotationsPresent = $methods !== [] || $hasAnyParamAttribute;

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

        return [
            'has' => $methods !== [] || $params !== [],
            'methods' => $methods,
            'params' => $params,
        ];
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
        $isArray = is_array($existingRules);
        $rulesList = $isArray
            ? $existingRules
            : ($existingRules !== '' ? explode('|', $existingRules) : []);

        $type = $parameter->getType();
        $isNullable = $type instanceof ReflectionNamedType && $type->allowsNull();

        // Build rule name set for O(1) lookups instead of iterating per check.
        $ruleNames = [];
        foreach ($rulesList as $rule) {
            $ruleNames[explode(':', $rule, 2)[0]] = true;
        }

        // Auto-complete 'required' if: no default value, not nullable, and not already present.
        if (!$parameter->isDefaultValueAvailable() && !$isNullable && !isset($ruleNames['required'])) {
            array_unshift($rulesList, 'required');
            $ruleNames['required'] = true;
        }

        // Auto-complete type rule if: has builtin type and no type rule present.
        if ($type instanceof ReflectionNamedType && $type->isBuiltin()) {
            $mappedRule = $this->mapBuiltinTypeToRule($type->getName());
            if ($mappedRule !== '' && !isset($ruleNames[$mappedRule])) {
                // Insert type rule after 'required' if present, otherwise at the beginning.
                $requiredIndex = array_search('required', $rulesList, true);
                if ($requiredIndex !== false) {
                    array_splice($rulesList, $requiredIndex + 1, 0, $mappedRule);
                } else {
                    array_unshift($rulesList, $mappedRule);
                }
                $ruleNames[$mappedRule] = true;
            }

            // Auto-complete 'nullable' if: type is nullable and not already present.
            if ($isNullable && !isset($ruleNames['nullable'])) {
                $rulesList[] = 'nullable';
            }
        }

        return $isArray ? $rulesList : implode('|', $rulesList);
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
