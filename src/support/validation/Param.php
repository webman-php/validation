<?php
declare(strict_types=1);

namespace support\validation;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Param extends \Webman\Validation\Param
{
}
