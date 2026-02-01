<?php
declare(strict_types=1);

namespace Webman\Validation\Command\ValidatorGenerator\Support;

use Webman\Validation\Command\ValidatorGenerator\Contracts\SchemaIntrospectorInterface;
use Webman\Validation\Command\ValidatorGenerator\Illuminate\MySqlInformationSchemaIntrospector;
use Webman\Validation\Command\ValidatorGenerator\Illuminate\PostgresInformationSchemaIntrospector;
use Webman\Validation\Command\ValidatorGenerator\Illuminate\SqlitePragmaIntrospector;
use Webman\Validation\Command\ValidatorGenerator\Illuminate\SqlServerIntrospector;

final class SchemaIntrospectorFactory
{
    public function createForDriver(string $driver): SchemaIntrospectorInterface
    {
        $driver = strtolower(trim($driver));

        return match ($driver) {
            'mysql', 'mariadb' => new MySqlInformationSchemaIntrospector(),
            'pgsql', 'postgres', 'postgresql' => new PostgresInformationSchemaIntrospector(),
            'sqlite' => new SqlitePragmaIntrospector(),
            'sqlsrv' => new SqlServerIntrospector(),
            default => throw new \RuntimeException("Unsupported database driver: {$driver}"),
        };
    }
}

