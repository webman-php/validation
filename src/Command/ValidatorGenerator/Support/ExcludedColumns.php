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

    /**
     * @return list<string>
     */
    public static function defaultForThinkOrm(): array
    {
        // ThinkORM (ThinkPHP) commonly uses *_time fields for timestamps/soft delete.
        return ['create_time', 'update_time', 'delete_time'];
    }
}

