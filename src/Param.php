<?php
declare(strict_types=1);

namespace Webman\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Param
{
    public function __construct(
        public string|array $rules = '',
        public array $messages = [],
        public string $attribute = '',
        public ?string $exception = null
    ) {
    }
}
