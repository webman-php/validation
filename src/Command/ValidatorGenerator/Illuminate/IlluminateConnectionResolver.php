<?php
declare(strict_types=1);

namespace Webman\Validation\Command\ValidatorGenerator\Illuminate;

use Illuminate\Database\ConnectionInterface;
use Webman\Validation\Command\ValidatorGenerator\Contracts\ConnectionResolverInterface;
use Webman\Validation\Command\ValidatorGenerator\Contracts\SchemaConnectionInterface;

final class IlluminateConnectionResolver implements ConnectionResolverInterface
{
    public function resolve(?string $connectionName = null): SchemaConnectionInterface
    {
        if (!class_exists(\support\Db::class)) {
            throw new \RuntimeException('Database support not found. Please install/enable webman/database plugin.');
        }

        $dbConfig = config('database', []);
        if (!is_array($dbConfig)) {
            throw new \RuntimeException('Invalid database config: config("database") must be an array.');
        }

        $connections = $dbConfig['connections'] ?? null;
        if (!is_array($connections) || $connections === []) {
            throw new \RuntimeException('Invalid database config: database.connections must be a non-empty array.');
        }

        $name = $connectionName;
        if ($name === null || trim($name) === '') {
            $default = $dbConfig['default'] ?? null;
            if (!is_string($default) || trim($default) === '') {
                throw new \RuntimeException('Database connection name not provided and database.default is not set.');
            }
            $name = trim($default);
        }

        $name = trim((string)$name);
        $connKey = $name;
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

            $pluginDb = config("plugin.$plugin.database", []);
            $pluginConnections = (is_array($pluginDb) ? ($pluginDb['connections'] ?? null) : null);
            if (is_array($pluginConnections) && $pluginConnections !== []) {
                if (!array_key_exists($conn, $pluginConnections)) {
                    $available = implode(', ', array_keys($pluginConnections));
                    throw new \RuntimeException("Database connection not found: {$name}. Available connections: {$available}");
                }
                $connKey = "plugin.$plugin.$conn";
            } else {
                // No plugin database connections: fallback to main project config.
                $connKey = $conn;
                if (!array_key_exists($connKey, $connections)) {
                    $available = implode(', ', array_keys($connections));
                    throw new \RuntimeException("Database connection not found: {$connKey}. Available connections: {$available}");
                }
            }
        } else {
            if (!array_key_exists($connKey, $connections)) {
                $available = implode(', ', array_keys($connections));
                throw new \RuntimeException("Database connection not found: {$connKey}. Available connections: {$available}");
            }
        }

        /** @var ConnectionInterface $connection */
        $connection = \support\Db::connection($connKey);
        return new IlluminateSchemaConnection($connection);
    }
}

