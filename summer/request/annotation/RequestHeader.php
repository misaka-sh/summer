<?php


namespace summer\request\annotation;

use Attribute;

/**
 * 请求映射请求头
 * @package summer\request\annotation
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class RequestHeader
{
    /**
     * 请求头映射
     * @param string $name 请求头字段名
     * @param bool $required 要求字段必须存在
     * @param mixed|null $defaultValue 如果不存在 则赋值 默认值
     */
    public function __construct(
        public string $name = "",
        public bool $required = true,
        public mixed $defaultValue = null
    )
    {
    }
}