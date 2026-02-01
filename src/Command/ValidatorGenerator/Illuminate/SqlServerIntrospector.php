<?php
declare(strict_types=1);

namespace Webman\Validation\Command\ValidatorGenerator\Illuminate;

use Illuminate\Database\ConnectionInterface;
use Webman\Validation\Command\ValidatorGenerator\Contracts\SchemaIntrospectorInterface;
use Webman\Validation\Command\ValidatorGenerator\DTO\ColumnDefinition;
use Webman\Validation\Command\ValidatorGenerator\DTO\TableDefinition;

final class SqlServerIntrospector implements SchemaIntrospectorInterface
{
    public function introspect(ConnectionInterface $connection, string $table): TableDefinition
    {
        $table = trim($table);
        if ($table === '') {
            throw new \InvalidArgumentException('Table name cannot be empty.');
        }

        [$schema, $tableName] = $this->splitSchemaTable($table);

        /** @var array<int, object> $rows */
        $rows = $connection->select(
            "SELECT
                c.name AS column_name,
                t.name AS data_type,
                CASE
                    WHEN t.name IN ('nvarchar','nchar') THEN c.max_length / 2
                    ELSE c.max_length
                END AS character_maximum_length,
                c.precision AS numeric_precision,
                c.scale AS numeric_scale,
                c.is_nullable AS is_nullable,
                dc.definition AS column_default,
                c.is_identity AS is_identity,
                ep.value AS column_comment
            FROM sys.columns c
            JOIN sys.tables tb ON tb.object_id = c.object_id
            JOIN sys.schemas s ON s.schema_id = tb.schema_id
            JOIN sys.types t ON t.user_type_id = c.user_type_id
            LEFT JOIN sys.default_constraints dc ON dc.parent_object_id = c.object_id AND dc.parent_column_id = c.column_id
            LEFT JOIN sys.extended_properties ep ON ep.major_id = c.object_id AND ep.minor_id = c.column_id AND ep.name = 'MS_Description'
            WHERE s.name = ?
              AND tb.name = ?
            ORDER BY c.column_id",
            [$schema, $tableName]
        );

        if ($rows === []) {
            throw new \RuntimeException("Table not found or has no columns: {$schema}.{$tableName}");
        }

        /** @var array<int, object> $pkRows */
        $pkRows = $connection->select(
            "SELECT c.name AS column_name
            FROM sys.indexes i
            JOIN sys.index_columns ic ON ic.object_id = i.object_id AND ic.index_id = i.index_id
            JOIN sys.columns c ON c.object_id = ic.object_id AND c.column_id = ic.column_id
            JOIN sys.tables tb ON tb.object_id = i.object_id
            JOIN sys.schemas s ON s.schema_id = tb.schema_id
            WHERE i.is_primary_key = 1
              AND s.name = ?
              AND tb.name = ?
            ORDER BY ic.key_ordinal",
            [$schema, $tableName]
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
            $nullable = (int)($row->is_nullable ?? 0) === 1;
            $defaultValue = $row->column_default ?? null;

            $charLen = $row->character_maximum_length ?? null;
            $characterMaximumLength = $charLen === null ? null : (int)$charLen;

            $precision = $row->numeric_precision ?? null;
            $numericPrecision = $precision === null ? null : (int)$precision;

            $scale = $row->numeric_scale ?? null;
            $numericScale = $scale === null ? null : (int)$scale;

            $autoIncrement = (int)($row->is_identity ?? 0) === 1;
            $comment = is_string($row->column_comment ?? null) ? (string)$row->column_comment : '';

            $columns[] = new ColumnDefinition(
                name: $name,
                dataType: $dataType,
                columnType: $dataType,
                nullable: $nullable,
                defaultValue: $defaultValue,
                characterMaximumLength: $characterMaximumLength,
                numericPrecision: $numericPrecision,
                numericScale: $numericScale,
                unsigned: false,
                autoIncrement: $autoIncrement,
                enumValues: [],
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
            return ['dbo', $parts[0]];
        }
        if (count($parts) === 2) {
            return [$parts[0], $parts[1]];
        }

        throw new \InvalidArgumentException("Invalid table name for SQL Server: {$table}");
    }
}

