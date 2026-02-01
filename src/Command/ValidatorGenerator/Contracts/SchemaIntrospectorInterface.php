<?php
declare(strict_types=1);

namespace Webman\Validation\Command\ValidatorGenerator\Contracts;

use Webman\Validation\Command\ValidatorGenerator\DTO\TableDefinition;

interface SchemaIntrospectorInterface
{
    /**
     * @throws \RuntimeException When schema cannot be read.
     */
    public function introspect(SchemaConnectionInterface $connection, string $table): TableDefinition;
}

