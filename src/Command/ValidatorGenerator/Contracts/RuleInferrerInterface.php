<?php
declare(strict_types=1);

namespace Webman\Validation\Command\ValidatorGenerator\Contracts;

use Webman\Validation\Command\ValidatorGenerator\DTO\TableDefinition;

interface RuleInferrerInterface
{
    /**
     * @param array{exclude_columns?: list<string>} $options
     * @return array{rules: array<string, string>, attributes: array<string, string>, scenes?: array<string, list<string>>}
     */
    public function infer(TableDefinition $table, array $options = []): array;
}

