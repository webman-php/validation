<?php
declare(strict_types=1);

namespace Webman\Validation\Command\ValidatorGenerator\DTO;

final class TableDefinition
{
    /**
     * @param list<ColumnDefinition> $columns
     * @param list<string> $primaryKeyColumns
     */
    public function __construct(
        public readonly string $table,
        public readonly array $columns,
        public readonly array $primaryKeyColumns = [],
    ) {
    }
}

