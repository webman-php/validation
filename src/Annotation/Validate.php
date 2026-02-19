<?php
declare(strict_types=1);

namespace Webman\Validation\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Validate
{
    public function __construct(
        public array $rules = [],
        public array $messages = [],
        public array $attributes = [],
        public ?string $validator = null,
        public ?string $scene = null
    ) {
    }
}
