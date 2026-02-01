<?php
declare(strict_types=1);

namespace Webman\Validation\Command\ValidatorGenerator\Contracts;

/**
 * A minimal DB connection abstraction for schema introspection.
 *
 * This intentionally avoids binding to a specific ORM (Illuminate/ThinkOrm).
 */
interface SchemaConnectionInterface
{
    public function driverName(): string;

    public function databaseName(): ?string;

    /**
     * @param array<int, mixed> $bindings
     * @return array<int, array<string, mixed>>
     */
    public function select(string $sql, array $bindings = []): array;
}

