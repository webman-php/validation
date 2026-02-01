<?php
declare(strict_types=1);

namespace Webman\Validation\Command\ValidatorGenerator\Support;

final class OrmDetector
{
    public const ORM_AUTO = 'auto';
    public const ORM_ILLUMINATE = 'illuminate';
    public const ORM_THINKORM = 'thinkorm';

    /**
     * @return array{illuminate: bool, thinkorm: bool}
     */
    public function availability(): array
    {
        return [
            'illuminate' => $this->isIlluminateAvailable(),
            'thinkorm' => $this->isThinkOrmAvailable(),
        ];
    }

    public function resolve(string $requestedOrm): string
    {
        $requestedOrm = strtolower(trim($requestedOrm));
        if ($requestedOrm === '' || $requestedOrm === self::ORM_AUTO) {
            $availability = $this->availability();
            if ($availability['illuminate']) {
                return self::ORM_ILLUMINATE;
            }
            if ($availability['thinkorm']) {
                return self::ORM_THINKORM;
            }
            throw new \RuntimeException('No ORM available. Please install/configure illuminate/database or think-orm.');
        }

        if ($requestedOrm === self::ORM_ILLUMINATE) {
            if (!$this->isIlluminateAvailable()) {
                throw new \RuntimeException('Requested orm=illuminate but illuminate/database is not available.');
            }
            return self::ORM_ILLUMINATE;
        }

        if ($requestedOrm === self::ORM_THINKORM) {
            if (!$this->isThinkOrmAvailable()) {
                throw new \RuntimeException('Requested orm=thinkorm but think-orm is not available.');
            }
            return self::ORM_THINKORM;
        }

        throw new \InvalidArgumentException("Unsupported orm value: {$requestedOrm}");
    }

    private function isIlluminateAvailable(): bool
    {
        if (!class_exists(\support\Db::class)) {
            return false;
        }

        $database = config('database');
        if (!is_array($database)) {
            return false;
        }

        // Follow webman make:model behavior: plugin.* defaults are not treated as local database config.
        $default = $database['default'] ?? null;
        if (is_string($default) && str_starts_with($default, 'plugin.')) {
            return false;
        }

        $connections = $database['connections'] ?? null;
        return is_array($connections) && $connections !== [];
    }

    private function isThinkOrmAvailable(): bool
    {
        $thinkorm = config('think-orm') ?: config('thinkorm');
        if (!is_array($thinkorm)) {
            return false;
        }

        $default = $thinkorm['default'] ?? null;
        if (is_string($default) && str_starts_with($default, 'plugin.')) {
            return false;
        }

        $connections = $thinkorm['connections'] ?? null;
        if (!is_array($connections) || $connections === []) {
            return false;
        }

        // Think-orm v2 uses support\think\Db; v1 uses think\facade\Db.
        $hasRuntime = class_exists(\support\think\Db::class) || class_exists(\think\facade\Db::class);
        if (!$hasRuntime) {
            return false;
        }

        return true;
    }
}

