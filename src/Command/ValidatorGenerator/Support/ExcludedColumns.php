<?php
declare(strict_types=1);

namespace Webman\Validation\Command\ValidatorGenerator\Support;

final class ExcludedColumns
{
    /**
     * @return list<string>
     */
    public static function defaultForIlluminate(): array
    {
        return ['created_at', 'updated_at', 'deleted_at'];
    }
}

