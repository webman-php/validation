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

        if (!array_key_exists($name, $connections)) {
            $available = implode(', ', array_keys($connections));
            throw new \RuntimeException("Database connection not found: {$name}. Available connections: {$available}");
        }

        /** @var ConnectionInterface $connection */
        $connection = \support\Db::connection($name);
        return new IlluminateSchemaConnection($connection);
    }
}

