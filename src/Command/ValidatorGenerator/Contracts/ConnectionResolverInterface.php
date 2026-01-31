<?php
declare(strict_types=1);

namespace Webman\Validation\Command\ValidatorGenerator\Contracts;

use Illuminate\Database\ConnectionInterface;

interface ConnectionResolverInterface
{
    /**
     * @throws \RuntimeException When connection cannot be resolved.
     */
    public function resolve(?string $connectionName = null): ConnectionInterface;
}

