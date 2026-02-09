<?php
declare(strict_types=1);

namespace Webman\Validation\Command\ValidatorGenerator\ThinkOrm;

use Webman\Validation\Command\ValidatorGenerator\Contracts\ConnectionResolverInterface;
use Webman\Validation\Command\ValidatorGenerator\Contracts\SchemaConnectionInterface;

final class ThinkOrmConnectionResolver implements ConnectionResolverInterface
{
    public function resolve(?string $connectionName = null): SchemaConnectionInterface
    {
        $thinkorm = config('think-orm');
        if (!is_array($thinkorm) || $thinkorm === []) {
            $alt = config('thinkorm');
            $thinkorm = is_array($alt) ? $alt : null;
        }
        if (!is_array($thinkorm)) {
            throw new \RuntimeException('Think-orm config not found: config("think-orm") or config("thinkorm").');
        }

        $mainConnections = $thinkorm['connections'] ?? null;
        if (!is_array($mainConnections) || $mainConnections === []) {
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

        $name = trim((string)$name);
        $connKey = $name;
        $connections = $mainConnections;

        if (str_starts_with($name, 'plugin.')) {
            // plugin.<plugin>.<connection>
            $parts = explode('.', $name, 3);
            $plugin = $parts[1] ?? '';
            $conn = $parts[2] ?? '';
            $plugin = is_string($plugin) ? trim($plugin) : '';
            $conn = is_string($conn) ? trim($conn) : '';
            if ($plugin === '' || $conn === '') {
                throw new \RuntimeException("Invalid plugin connection name: {$name}");
            }

            $pluginCfg = config("plugin.$plugin.thinkorm");
            if (!is_array($pluginCfg) || $pluginCfg === []) {
                $alt = config("plugin.$plugin.think-orm");
                $pluginCfg = is_array($alt) ? $alt : [];
            }
            $pluginConnections = $pluginCfg['connections'] ?? null;
            if (is_array($pluginConnections) && $pluginConnections !== []) {
                $connections = $pluginConnections;
                $connKey = "plugin.$plugin.$conn";
                $name = $conn;
            } else {
                // No plugin think-orm connections: fallback to main project config.
                $connections = $mainConnections;
                $connKey = $conn;
                $name = $conn;
            }
        }

        if (!array_key_exists($name, $connections)) {
            $available = implode(', ', array_keys($connections));
            throw new \RuntimeException("Think-orm connection not found: {$name}. Available connections: {$available}");
        }

        /** @var array<string, mixed> $cfg */
        $cfg = is_array($connections[$name]) ? $connections[$name] : [];
        $driver = (string)($cfg['type'] ?? 'mysql');
        $database = isset($cfg['database']) ? (string)$cfg['database'] : null;

        $connection = $this->connect($connKey);
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

