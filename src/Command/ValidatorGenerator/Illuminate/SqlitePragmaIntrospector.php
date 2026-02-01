<?php
declare(strict_types=1);

namespace Webman\Validation\Command\ValidatorGenerator\Illuminate;

use Illuminate\Database\ConnectionInterface;
use Webman\Validation\Command\ValidatorGenerator\Contracts\SchemaIntrospectorInterface;
use Webman\Validation\Command\ValidatorGenerator\DTO\ColumnDefinition;
use Webman\Validation\Command\ValidatorGenerator\DTO\TableDefinition;

final class SqlitePragmaIntrospector implements SchemaIntrospectorInterface
{
    public function introspect(ConnectionInterface $connection, string $table): TableDefinition
    {
        $table = trim($table);
        if ($table === '') {
            throw new \InvalidArgumentException('Table name cannot be empty.');
        }
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $table)) {
            throw new \InvalidArgumentException("Invalid table name for SQLite: {$table}");
        }

        /** @var array<int, object> $rows */
        $rows = $connection->select("PRAGMA table_info('{$table}')");
        if ($rows === []) {
            throw new \RuntimeException("Table not found or has no columns: {$table}");
        }

        $primaryKeyColumns = [];
        $columns = [];

        foreach ($rows as $row) {
            $name = (string)($row->name ?? '');
            if ($name === '') {
                continue;
            }

            $type = strtolower((string)($row->type ?? ''));
            $notNull = (int)($row->notnull ?? 0) === 1;
            $nullable = !$notNull;
            $defaultValue = $row->dflt_value ?? null;
            $pk = (int)($row->pk ?? 0);
            if ($pk > 0) {
                $primaryKeyColumns[] = $name;
            }

            [$dataType, $charLen, $precision, $scale] = $this->parseType($type);

            $autoIncrement = false;
            // Best-effort: INTEGER PRIMARY KEY behaves like auto-increment rowid.
            if ($pk > 0 && $dataType === 'integer') {
                $autoIncrement = true;
            }

            $columns[] = new ColumnDefinition(
                name: $name,
                dataType: $dataType,
                columnType: $type !== '' ? $type : $dataType,
                nullable: $nullable,
                defaultValue: $defaultValue,
                characterMaximumLength: $charLen,
                numericPrecision: $precision,
                numericScale: $scale,
                unsigned: false,
                autoIncrement: $autoIncrement,
                enumValues: [],
                comment: '',
            );
        }

        return new TableDefinition($table, $columns, $primaryKeyColumns);
    }

    /**
     * @return array{0:string,1:?int,2:?int,3:?int} [dataType, charLen, precision, scale]
     */
    private function parseType(string $type): array
    {
        $type = strtolower(trim($type));
        if ($type === '') {
            return ['string', null, null, null];
        }

        if (str_contains($type, 'int')) {
            return ['integer', null, null, null];
        }
        if (str_contains($type, 'char') || str_contains($type, 'clob') || str_contains($type, 'text')) {
            $len = null;
            if (preg_match('/\((\d+)\)/', $type, $m)) {
                $len = (int)$m[1];
            }
            return ['varchar', $len, null, null];
        }
        if (str_contains($type, 'blob')) {
            return ['string', null, null, null];
        }
        if (str_contains($type, 'real') || str_contains($type, 'floa') || str_contains($type, 'doub')) {
            return ['double', null, null, null];
        }
        if (str_contains($type, 'dec') || str_contains($type, 'num')) {
            $precision = null;
            $scale = null;
            if (preg_match('/\((\d+)\s*,\s*(\d+)\)/', $type, $m)) {
                $precision = (int)$m[1];
                $scale = (int)$m[2];
            } elseif (preg_match('/\((\d+)\)/', $type, $m)) {
                $precision = (int)$m[1];
            }
            return ['decimal', null, $precision, $scale];
        }
        if (str_contains($type, 'bool')) {
            return ['boolean', null, null, null];
        }
        if (str_contains($type, 'date') || str_contains($type, 'time')) {
            return ['datetime', null, null, null];
        }

        return ['string', null, null, null];
    }
}

