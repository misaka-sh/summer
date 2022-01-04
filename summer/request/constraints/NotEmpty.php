<?php


namespace summer\request\constraints;

use Attribute;

/**
 * 不为空
 * @package summer\request\constraints
 */
#[Attribute(Attribute::TARGET_PROPERTY|Attribute::TARGET_PARAMETER)]
class NotEmpty
{
    /**
     * NotEmpty constructor.
     * @param string $message 输出的信息
     */
    public function __construct(
        public string $message = "不能为空"
    )
    {
    }
}