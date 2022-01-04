<?php


namespace summer\request\constraints;

use Attribute;

/**
 * 不为null
 * @package summer\request\constraints
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class NotNull
{
    /**
     * NotEmpty constructor.
     * @param string $message 输出的信息
     */
    public function __construct(
        public string $message = "不能为null"
    )
    {
    }
}