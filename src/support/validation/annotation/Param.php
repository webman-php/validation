<?php
declare(strict_types=1);

namespace support\validation\annotation;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Param extends \Webman\Validation\Annotation\Param
{
}
