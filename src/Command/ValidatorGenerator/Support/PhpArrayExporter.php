<?php
declare(strict_types=1);

namespace Webman\Validation\Command\ValidatorGenerator\Support;

final class PhpArrayExporter
{
    public static function export(array $value, int $indentLevel = 1): string
    {
        return self::exportArray($value, $indentLevel);
    }

    /**
     * Export an array for use as a class property initializer.
     *
     * Expected output style:
     * - Empty: []
     * - Non-empty:
     *   [
     *       'k' => 'v',
     *   ]
     */
    public static function exportForProperty(array $value, int $propertyIndentLevel = 1): string
    {
        if ($value === []) {
            return '[]';
        }

        $propertyIndent = str_repeat('    ', $propertyIndentLevel);
        $childIndent = str_repeat('    ', $propertyIndentLevel + 1);

        $lines = ['['];
        foreach ($value as $k => $v) {
            $key = is_int($k) ? null : self::exportScalar($k);

            if (is_array($v)) {
                $exported = self::exportArrayValue($v, $propertyIndentLevel + 1);
            } else {
                $exported = self::exportScalar($v);
            }

            if ($key === null) {
                $lines[] = "{$childIndent}{$exported},";
            } else {
                $lines[] = "{$childIndent}{$key} => {$exported},";
            }
        }
        $lines[] = "{$propertyIndent}]";

        return implode("\n", $lines);
    }

    private static function exportArray(array $value, int $indentLevel): string
    {
        if ($value === []) {
            return '[]';
        }

        $indent = str_repeat('    ', $indentLevel);
        $childIndent = str_repeat('    ', $indentLevel + 1);

        $lines = ["{$indent}["];
        foreach ($value as $k => $v) {
            $key = is_int($k) ? null : self::exportScalar($k);

            if (is_array($v)) {
                $exported = self::exportArray($v, $indentLevel + 1);
            } else {
                $exported = self::exportScalar($v);
            }

            if ($key === null) {
                $lines[] = "{$childIndent}{$exported},";
            } else {
                $lines[] = "{$childIndent}{$key} => {$exported},";
            }
        }
        $lines[] = "{$indent}]";
        return implode("\n", $lines);
    }

    /**
     * Export an array as a value after `=> `, i.e. first line has no leading indent.
     */
    private static function exportArrayValue(array $value, int $closingIndentLevel): string
    {
        if ($value === []) {
            return '[]';
        }

        $closingIndent = str_repeat('    ', $closingIndentLevel);
        $childIndent = str_repeat('    ', $closingIndentLevel + 1);

        $lines = ['['];
        foreach ($value as $k => $v) {
            $key = is_int($k) ? null : self::exportScalar($k);

            if (is_array($v)) {
                $exported = self::exportArrayValue($v, $closingIndentLevel + 1);
            } else {
                $exported = self::exportScalar($v);
            }

            if ($key === null) {
                $lines[] = "{$childIndent}{$exported},";
            } else {
                $lines[] = "{$childIndent}{$key} => {$exported},";
            }
        }
        $lines[] = "{$closingIndent}]";
        return implode("\n", $lines);
    }

    private static function exportScalar(mixed $value): string
    {
        if ($value === null) {
            return 'null';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_int($value) || is_float($value)) {
            return (string)$value;
        }
        if (is_string($value)) {
            return "'" . str_replace(["\\", "'"], ["\\\\", "\\'"], $value) . "'";
        }

        // Fallback to string casting for unexpected scalars.
        return "'" . str_replace(["\\", "'"], ["\\\\", "\\'"], (string)$value) . "'";
    }
}

