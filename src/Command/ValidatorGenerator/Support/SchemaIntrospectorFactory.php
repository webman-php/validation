<?php
declare(strict_types=1);

namespace Webman\Validation\Command\ValidatorGenerator\Support;

use Webman\Validation\Command\ValidatorGenerator\Contracts\SchemaIntrospectorInterface;
use Webman\Validation\Command\ValidatorGenerator\Illuminate\MySqlInformationSchemaIntrospector;

final class SchemaIntrospectorFactory
{
    public function createForDriver(string $driver): SchemaIntrospectorInterface
    {
        $driver = strtolower(trim($driver));

        return match ($driver) {
            'mysql', 'mariadb' => new MySqlInformationSchemaIntrospector(),
            default => throw new \RuntimeException("Unsupported database driver: {$driver}"),
        };
    }
}

