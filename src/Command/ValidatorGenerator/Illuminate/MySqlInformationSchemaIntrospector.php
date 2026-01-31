<?php
declare(strict_types=1);

namespace Webman\Validation\Command\ValidatorGenerator\Illuminate;

use Illuminate\Database\ConnectionInterface;
use Webman\Validation\Command\ValidatorGenerator\Contracts\SchemaIntrospectorInterface;
use Webman\Validation\Command\ValidatorGenerator\DTO\ColumnDefinition;
use Webman\Validation\Command\ValidatorGenerator\DTO\TableDefinition;

final class MySqlInformationSchemaIntrospector implements SchemaIntrospectorInterface
{
    public function introspect(ConnectionInterface $connection, string $table): TableDefinition
    {
        $table = trim($table);
        if ($table === '') {
            throw new \InvalidArgumentException('Table name cannot be empty.');
        }

        $database = (string)$connection->getDatabaseName();
        if ($database === '') {
            throw new \RuntimeException('Database name is empty for current connection.');
        }

        /** @var array<int, object> $rows */
        $rows = $connection->select(
            "SELECT
                COLUMN_NAME AS column_name,
                DATA_TYPE AS data_type,
                COLUMN_TYPE AS column_type,
                IS_NULLABLE AS is_nullable,
                COLUMN_DEFAULT AS column_default,
                CHARACTER_MAXIMUM_LENGTH AS character_maximum_length,
                NUMERIC_PRECISION AS numeric_precision,
                NUMERIC_SCALE AS numeric_scale,
                EXTRA AS extra,
                COLUMN_COMMENT AS column_comment
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = ?
            ORDER BY ORDINAL_POSITION",
            [$database, $table]
        );

        if ($rows === []) {
            throw new \RuntimeException("Table not found or has no columns: {$database}.{$table}");
        }

        /** @var array<int, object> $pkRows */
        $pkRows = $connection->select(
            "SELECT
                COLUMN_NAME AS column_name
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = ?
              AND CONSTRAINT_NAME = 'PRIMARY'
            ORDER BY ORDINAL_POSITION",
            [$database, $table]
        );
        $primaryKeyColumns = [];
        foreach ($pkRows as $pkRow) {
            $pkName = (string)($pkRow->column_name ?? '');
            if ($pkName !== '') {
                $primaryKeyColumns[] = $pkName;
            }
        }

        $columns = [];
        foreach ($rows as $row) {
            $name = (string)($row->column_name ?? '');
            if ($name === '') {
                continue;
            }

            $dataType = strtolower((string)($row->data_type ?? ''));
            $columnType = strtolower((string)($row->column_type ?? $dataType));
            $nullable = strtoupper((string)($row->is_nullable ?? 'NO')) === 'YES';
            $defaultValue = $row->column_default ?? null;

            $charLen = $row->character_maximum_length ?? null;
            $characterMaximumLength = $charLen === null ? null : (int)$charLen;

            $precision = $row->numeric_precision ?? null;
            $numericPrecision = $precision === null ? null : (int)$precision;

            $scale = $row->numeric_scale ?? null;
            $numericScale = $scale === null ? null : (int)$scale;

            $extra = strtolower((string)($row->extra ?? ''));
            $autoIncrement = str_contains($extra, 'auto_increment');
            $unsigned = str_contains($columnType, 'unsigned');

            $comment = (string)($row->column_comment ?? '');

            $enumValues = $this->parseEnumValues($dataType, $columnType);

            $columns[] = new ColumnDefinition(
                name: $name,
                dataType: $dataType,
                columnType: $columnType,
                nullable: $nullable,
                defaultValue: $defaultValue,
                characterMaximumLength: $characterMaximumLength,
                numericPrecision: $numericPrecision,
                numericScale: $numericScale,
                unsigned: $unsigned,
                autoIncrement: $autoIncrement,
                enumValues: $enumValues,
                comment: $comment,
            );
        }

        return new TableDefinition($table, $columns, $primaryKeyColumns);
    }

    /**
     * @return list<string>
     */
    private function parseEnumValues(string $dataType, string $columnType): array
    {
        if ($dataType !== 'enum' && !str_starts_with($columnType, 'enum(')) {
            return [];
        }

        // column_type example: enum('a','b','c')
        if (!preg_match('/^enum\((.*)\)$/i', $columnType, $m)) {
            return [];
        }

        $inner = $m[1];
        // Match MySQL quoted strings inside enum(...)
        if (!preg_match_all("/'((?:\\\\'|[^'])*)'/", $inner, $matches)) {
            return [];
        }

        $values = [];
        foreach ($matches[1] as $v) {
            $values[] = str_replace("\\'", "'", (string)$v);
        }
        return $values;
    }
}

