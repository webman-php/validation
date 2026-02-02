<?php
declare(strict_types=1);

namespace Webman\Validation\Middleware;

use InvalidArgumentException;
use ReflectionMethod;
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
        foreach ($method->getAttributes(Validate::class, \ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            /** @var Validate $config */
            $config = $attribute->newInstance();
            $methods[] = $config;
        }

        $params = [];
        foreach ($method->getParameters() as $parameter) {
            foreach ($parameter->getAttributes(Param::class, \ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
                /** @var Param $config */
                $config = $attribute->newInstance();
                $hasDefault = $parameter->isDefaultValueAvailable();
                $params[] = [
                    'name' => $parameter->getName(),
                    'config' => $config,
                    'hasDefault' => $hasDefault,
                    'default' => $hasDefault ? $parameter->getDefaultValue() : null,
                ];
            }
        }

        $metadata = [
            'has' => $methods !== [] || $params !== [],
            'methods' => $methods,
            'params' => $params,
        ];

        return self::$metadataCache[$key] = $metadata;
    }
}
