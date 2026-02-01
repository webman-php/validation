<?php
declare(strict_types=1);

namespace Webman\Validation\Command\ValidatorGenerator\ThinkOrm;

use Webman\Validation\Command\ValidatorGenerator\Contracts\SchemaConnectionInterface;

final class ThinkOrmSchemaConnection implements SchemaConnectionInterface
{
    /**
     * @param object $connection Think-orm connection instance.
     */
    public function __construct(
        private readonly object $connection,
        private readonly string $driver,
        private readonly ?string $database,
    ) {
    }

    public function driverName(): string
    {
        return $this->driver;
    }

    public function databaseName(): ?string
    {
        return $this->database;
    }

    public function select(string $sql, array $bindings = []): array
    {
        // Think-orm uses `query` for SELECT statements.
        if (!method_exists($this->connection, 'query')) {
            throw new \RuntimeException('Think-orm connection does not support query().');
        }

        /** @var mixed $rows */
        $rows = $this->connection->query($sql, $bindings);
        if (!is_array($rows)) {
            return [];
        }

        $result = [];
        foreach ($rows as $row) {
            if (is_array($row)) {
                $result[] = $row;
                continue;
            }
            if (is_object($row)) {
                /** @var array<string, mixed> $arr */
                $arr = get_object_vars($row);
                $result[] = $arr;
                continue;
            }
            $result[] = ['value' => $row];
        }

        return $result;
    }
}

