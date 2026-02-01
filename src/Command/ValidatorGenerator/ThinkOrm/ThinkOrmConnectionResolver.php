<?php
declare(strict_types=1);

namespace Webman\Validation\Command\ValidatorGenerator\ThinkOrm;

use Webman\Validation\Command\ValidatorGenerator\Contracts\ConnectionResolverInterface;
use Webman\Validation\Command\ValidatorGenerator\Contracts\SchemaConnectionInterface;

final class ThinkOrmConnectionResolver implements ConnectionResolverInterface
{
    public function resolve(?string $connectionName = null): SchemaConnectionInterface
    {
        $thinkorm = config('think-orm') ?: config('thinkorm');
        if (!is_array($thinkorm)) {
            throw new \RuntimeException('Think-orm config not found: config("think-orm") or config("thinkorm").');
        }

        $connections = $thinkorm['connections'] ?? null;
        if (!is_array($connections) || $connections === []) {
            throw new \RuntimeException('Invalid think-orm config: connections must be a non-empty array.');
        }

        $name = $connectionName;
        if ($name === null || trim($name) === '') {
            $default = $thinkorm['default'] ?? null;
            if (!is_string($default) || trim($default) === '') {
                throw new \RuntimeException('Think-orm connection name not provided and think-orm.default is not set.');
            }
            $name = trim($default);
        }

        if (!array_key_exists($name, $connections)) {
            $available = implode(', ', array_keys($connections));
            throw new \RuntimeException("Think-orm connection not found: {$name}. Available connections: {$available}");
        }

        /** @var array<string, mixed> $cfg */
        $cfg = is_array($connections[$name]) ? $connections[$name] : [];
        $driver = (string)($cfg['type'] ?? 'mysql');
        $database = isset($cfg['database']) ? (string)$cfg['database'] : null;

        $connection = $this->connect($name);
        return new ThinkOrmSchemaConnection($connection, strtolower($driver), $database);
    }

    private function connect(string $name): object
    {
        // Think-orm v2
        if (class_exists(\support\think\Db::class)) {
            /** @var object $conn */
            $conn = \support\think\Db::connect($name);
            return $conn;
        }

        // Think-orm v1
        if (class_exists(\think\facade\Db::class)) {
            /** @var object $conn */
            $conn = \think\facade\Db::connect($name);
            return $conn;
        }

        throw new \RuntimeException('Think-orm is not installed. Missing support\\think\\Db or think\\facade\\Db.');
    }
}

