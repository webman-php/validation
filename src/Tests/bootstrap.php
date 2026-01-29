<?php
declare(strict_types=1);

if (!function_exists('validation_test_set_config')) {
    function validation_test_set_config(array $overrides = []): void
    {
        $defaults = [
            'translation' => [
                'locale' => 'en',
                'fallback_locale' => ['en'],
                'path' => '',
            ],
            'plugin' => [
                'webman' => [
                    'validation' => [
                        'app' => [
                            'enable' => true,
                            'exception' => \support\validation\ValidationException::class,
                        ],
                    ],
                ],
            ],
        ];

        $current = $GLOBALS['validation_test_config'] ?? [];
        $merged = array_replace_recursive($defaults, $current, $overrides);
        $GLOBALS['validation_test_config'] = $merged;

        if (class_exists(\Webman\Config::class)) {
            $property = new ReflectionProperty(\Webman\Config::class, 'config');
            $property->setAccessible(true);
            $property->setValue(null, $merged);
        }
    }
}

validation_test_set_config();

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        $config = $GLOBALS['validation_test_config'] ?? [];
        if ($key === null || $key === '') {
            return $config;
        }

        $parts = explode('.', $key);
        $value = $config;
        foreach ($parts as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return $default;
            }
            $value = $value[$part];
        }
        return $value;
    }
}

