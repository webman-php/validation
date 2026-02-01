<?php
declare(strict_types=1);

namespace Webman\Validation\Command\ValidatorGenerator\Illuminate;

use Webman\Validation\Command\ValidatorGenerator\Contracts\SchemaConnectionInterface;
use Webman\Validation\Command\ValidatorGenerator\Contracts\SchemaIntrospectorInterface;
use Webman\Validation\Command\ValidatorGenerator\DTO\ColumnDefinition;
use Webman\Validation\Command\ValidatorGenerator\DTO\TableDefinition;

final class PostgresInformationSchemaIntrospector implements SchemaIntrospectorInterface
{
    public function introspect(SchemaConnectionInterface $connection, string $table): TableDefinition
    {
        $table = trim($table);
        if ($table === '') {
            throw new \InvalidArgumentException('Table name cannot be empty.');
        }

        [$schema, $tableName] = $this->splitSchemaTable($table);

        $rows = $connection->select(
            "SELECT
                c.column_name AS column_name,
                c.data_type AS data_type,
                c.udt_name AS udt_name,
                c.is_nullable AS is_nullable,
                c.column_default AS column_default,
                c.character_maximum_length AS character_maximum_length,
                c.numeric_precision AS numeric_precision,
                c.numeric_scale AS numeric_scale
            FROM information_schema.columns c
            WHERE c.table_schema = ?
              AND c.table_name = ?
            ORDER BY c.ordinal_position",
            [$schema, $tableName]
        );

        if ($rows === []) {
            throw new \RuntimeException("Table not found or has no columns: {$schema}.{$tableName}");
        }

        $pkRows = $connection->select(
            "SELECT kcu.column_name AS column_name
            FROM information_schema.table_constraints tc
            JOIN information_schema.key_column_usage kcu
              ON tc.constraint_name = kcu.constraint_name
             AND tc.table_schema = kcu.table_schema
             AND tc.table_name = kcu.table_name
            WHERE tc.constraint_type = 'PRIMARY KEY'
              AND tc.table_schema = ?
              AND tc.table_name = ?
            ORDER BY kcu.ordinal_position",
            [$schema, $tableName]
        );
        $primaryKeyColumns = [];
        foreach ($pkRows as $pkRow) {
            $pkName = (string)($pkRow['column_name'] ?? '');
            if ($pkName !== '') {
                $primaryKeyColumns[] = $pkName;
            }
        }

        // Fetch column comments (best-effort).
        $commentRows = $connection->select(
            "SELECT
                a.attname AS column_name,
                COALESCE(col_description(a.attrelid, a.attnum), '') AS column_comment
            FROM pg_attribute a
            JOIN pg_class t ON t.oid = a.attrelid
            JOIN pg_namespace n ON n.oid = t.relnamespace
            WHERE n.nspname = ?
              AND t.relname = ?
              AND a.attnum > 0
              AND NOT a.attisdropped",
            [$schema, $tableName]
        );
        $commentsByColumn = [];
        foreach ($commentRows as $cr) {
            $cn = (string)($cr['column_name'] ?? '');
            if ($cn !== '') {
                $commentsByColumn[$cn] = (string)($cr['column_comment'] ?? '');
            }
        }

        // Fetch enum values (best-effort) for user-defined enum types.
        $enumValuesByType = $this->loadEnumValuesByType($connection);

        $columns = [];
        foreach ($rows as $row) {
            $name = (string)($row['column_name'] ?? '');
            if ($name === '') {
                continue;
            }

            $dataType = strtolower((string)($row['data_type'] ?? ''));
            $udtName = strtolower((string)($row['udt_name'] ?? ''));
            $nullable = strtoupper((string)($row['is_nullable'] ?? 'NO')) === 'YES';
            $defaultValue = $row['column_default'] ?? null;

            $charLen = $row['character_maximum_length'] ?? null;
            $characterMaximumLength = $charLen === null ? null : (int)$charLen;

            $precision = $row['numeric_precision'] ?? null;
            $numericPrecision = $precision === null ? null : (int)$precision;

            $scale = $row['numeric_scale'] ?? null;
            $numericScale = $scale === null ? null : (int)$scale;

            $autoIncrement = false;
            if (is_string($defaultValue) && str_contains($defaultValue, 'nextval(')) {
                $autoIncrement = true;
            }

            $comment = (string)($commentsByColumn[$name] ?? '');

            $enumValues = [];
            if ($dataType === 'user-defined' && isset($enumValuesByType[$udtName])) {
                $enumValues = $enumValuesByType[$udtName];
                $dataType = 'enum';
            }

            $columns[] = new ColumnDefinition(
                name: $name,
                dataType: $dataType,
                columnType: $udtName !== '' ? $udtName : $dataType,
                nullable: $nullable,
                defaultValue: $defaultValue,
                characterMaximumLength: $characterMaximumLength,
                numericPrecision: $numericPrecision,
                numericScale: $numericScale,
                unsigned: false,
                autoIncrement: $autoIncrement,
                enumValues: $enumValues,
                comment: $comment,
            );
        }

        return new TableDefinition($tableName, $columns, $primaryKeyColumns);
    }

    /**
     * @return array{0:string,1:string} [schema, table]
     */
    private function splitSchemaTable(string $table): array
    {
        $parts = array_values(array_filter(explode('.', $table), static fn(string $p): bool => $p !== ''));
        if (count($parts) === 1) {
            return ['public', $parts[0]];
        }
        if (count($parts) === 2) {
            return [$parts[0], $parts[1]];
        }

        throw new \InvalidArgumentException("Invalid table name for Postgres: {$table}");
    }

    /**
     * @return array<string, list<string>> map udt_name => enum labels
     */
    private function loadEnumValuesByType(SchemaConnectionInterface $connection): array
    {
        $rows = $connection->select(
            "SELECT
                t.typname AS type_name,
                e.enumlabel AS enum_label
            FROM pg_type t
            JOIN pg_enum e ON e.enumtypid = t.oid
            ORDER BY t.typname, e.enumsortorder"
        );

        $map = [];
        foreach ($rows as $row) {
            $type = strtolower((string)($row['type_name'] ?? ''));
            $label = (string)($row['enum_label'] ?? '');
            if ($type === '' || $label === '') {
                continue;
            }
            $map[$type] ??= [];
            $map[$type][] = $label;
        }

        return $map;
    }
}

