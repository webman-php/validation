<?php
declare(strict_types=1);

namespace Webman\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Param
{
    public function __construct(
        public string $rules = '',
        public array $messages = [],
        public string $attribute = ''
    ) {
    }
}
