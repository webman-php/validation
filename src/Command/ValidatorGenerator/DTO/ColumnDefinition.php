<?php
declare(strict_types=1);

namespace Webman\Validation\Command\ValidatorGenerator\DTO;

final class ColumnDefinition
{
    /**
     * @param list<string> $enumValues
     */
    public function __construct(
        public readonly string $name,
        public readonly string $dataType,
        public readonly string $columnType,
        public readonly bool $nullable,
        public readonly mixed $defaultValue,
        public readonly ?int $characterMaximumLength,
        public readonly ?int $numericPrecision,
        public readonly ?int $numericScale,
        public readonly bool $unsigned,
        public readonly bool $autoIncrement,
        public readonly array $enumValues,
        public readonly string $comment,
    ) {
    }
}

