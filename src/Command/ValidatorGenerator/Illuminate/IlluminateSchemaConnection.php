<?php
declare(strict_types=1);

namespace Webman\Validation\Command\ValidatorGenerator\Illuminate;

use Illuminate\Database\ConnectionInterface;
use Webman\Validation\Command\ValidatorGenerator\Contracts\SchemaConnectionInterface;

final class IlluminateSchemaConnection implements SchemaConnectionInterface
{
    public function __construct(private readonly ConnectionInterface $connection)
    {
    }

    public function driverName(): string
    {
        return (string)$this->connection->getDriverName();
    }

    public function databaseName(): ?string
    {
        $name = (string)$this->connection->getDatabaseName();
        return $name !== '' ? $name : null;
    }

    public function select(string $sql, array $bindings = []): array
    {
        /** @var array<int, mixed> $rows */
        $rows = $this->connection->select($sql, $bindings);

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

