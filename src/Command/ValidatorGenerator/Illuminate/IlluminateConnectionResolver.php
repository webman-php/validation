<?php
declare(strict_types=1);

namespace Webman\Validation\Command\ValidatorGenerator\Illuminate;

use Illuminate\Database\ConnectionInterface;
use Webman\Validation\Command\ValidatorGenerator\Contracts\ConnectionResolverInterface;

final class IlluminateConnectionResolver implements ConnectionResolverInterface
{
    public function resolve(?string $connectionName = null): ConnectionInterface
    {
        if (!class_exists(\support\Db::class)) {
            throw new \RuntimeException('Database support not found. Please install/enable webman/database plugin.');
        }

        /** @var ConnectionInterface $connection */
        $connection = \support\Db::connection($connectionName);
        return $connection;
    }
}

