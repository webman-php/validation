<?php
declare(strict_types=1);

namespace Webman\Validation\Command\ValidatorGenerator\Rules;

use Webman\Validation\Command\ValidatorGenerator\Contracts\RuleInferrerInterface;
use Webman\Validation\Command\ValidatorGenerator\DTO\ColumnDefinition;
use Webman\Validation\Command\ValidatorGenerator\DTO\TableDefinition;

final class DefaultRuleInferrer implements RuleInferrerInterface
{
    public function infer(TableDefinition $table, array $options = []): array
    {
        $exclude = array_map('strtolower', $options['exclude_columns'] ?? []);
        $excludeMap = array_fill_keys($exclude, true);
        $withScenes = (bool)($options['with_scenes'] ?? false);

        $rules = [];
        $attributes = [];

        foreach ($table->columns as $column) {
            if (isset($excludeMap[strtolower($column->name)])) {
                continue;
            }
            if ($column->autoIncrement && !$withScenes) {
                // For create/update validators we generally don't validate auto-increment columns (e.g. id).
                continue;
            }

            $ruleParts = $this->inferRuleParts($column);
            if ($ruleParts === []) {
                continue;
            }

            $rules[$column->name] = implode('|', $ruleParts);

            $comment = trim($column->comment);
            if ($comment !== '') {
                $attributes[$column->name] = $comment;
            }
        }

        $result = ['rules' => $rules, 'attributes' => $attributes];

        if ($withScenes) {
            $scenesType = strtolower(trim((string)($options['scenes'] ?? 'crud')));
            if ($scenesType !== 'crud') {
                throw new \InvalidArgumentException("Unsupported scenes type: {$scenesType}");
            }

            $result['scenes'] = $this->buildCrudScenes($table, $rules);
        }

        return $result;
    }

    /**
     * @param array<string, string> $rules
     * @return array<string, list<string>>
     */
    private function buildCrudScenes(TableDefinition $table, array $rules): array
    {
        $ruleKeys = array_keys($rules);
        $pk = array_values(array_intersect($table->primaryKeyColumns, $ruleKeys));
        if ($pk === []) {
            throw new \RuntimeException("Cannot generate CRUD scenes: primary key columns not found in rules for table {$table->table}");
        }

        $nonPk = array_values(array_diff($ruleKeys, $pk));

        return [
            'create' => $nonPk,
            // Full update (PUT semantics) generally requires primary key + full payload.
            'update' => array_values(array_merge($pk, $nonPk)),
            'delete' => $pk,
            'detail' => $pk,
        ];
    }

    /**
     * @return list<string>
     */
    private function inferRuleParts(ColumnDefinition $column): array
    {
        $parts = [];

        if ($this->shouldBeRequired($column)) {
            $parts[] = 'required';
        } elseif ($column->nullable) {
            $parts[] = 'nullable';
        }

        $typeParts = $this->inferTypeParts($column);
        foreach ($typeParts as $p) {
            $parts[] = $p;
        }

        return $parts;
    }

    private function shouldBeRequired(ColumnDefinition $column): bool
    {
        if ($column->nullable) {
            return false;
        }
        // If default exists, allow omitting the field.
        if ($column->defaultValue !== null) {
            return false;
        }

        return true;
    }

    /**
     * @return list<string>
     */
    private function inferTypeParts(ColumnDefinition $column): array
    {
        $type = strtolower($column->dataType);

        if ($column->enumValues !== []) {
            // Values containing commas are not representable; keep best-effort.
            return ['in:' . implode(',', $column->enumValues)];
        }

        return match ($type) {
            'varchar', 'char' => $this->stringRules($column),
            'text', 'tinytext', 'mediumtext', 'longtext' => ['string'],
            'int', 'integer', 'tinyint', 'smallint', 'mediumint', 'bigint' => $this->integerRules($column),
            'decimal', 'numeric', 'float', 'double' => $this->numericRules($column),
            'date', 'datetime', 'timestamp', 'time' => ['date'],
            'json' => ['json'],
            'bool', 'boolean' => ['boolean'],
            default => [],
        };
    }

    /**
     * @return list<string>
     */
    private function stringRules(ColumnDefinition $column): array
    {
        $rules = ['string'];
        if ($column->characterMaximumLength !== null && $column->characterMaximumLength > 0) {
            $rules[] = 'max:' . $column->characterMaximumLength;
        }
        return $rules;
    }

    /**
     * @return list<string>
     */
    private function integerRules(ColumnDefinition $column): array
    {
        $rules = ['integer'];
        if ($column->unsigned) {
            $rules[] = 'min:0';
        }
        return $rules;
    }

    /**
     * @return list<string>
     */
    private function numericRules(ColumnDefinition $column): array
    {
        $rules = ['numeric'];
        if ($column->unsigned) {
            $rules[] = 'min:0';
        }
        return $rules;
    }
}

