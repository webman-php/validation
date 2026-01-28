<?php
declare(strict_types=1);

namespace support\validation;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Validate extends \Webman\Validation\Validate
{
}
