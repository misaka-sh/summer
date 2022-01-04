<?php


namespace summer\response\annotation;

use Attribute;
use JetBrains\PhpStorm\ExpectedValues;

/**
 * 响应体
 * @package summer\response\annotation
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class ResponseBody
{
    /**
     * 响应体
     * @param int $type 类型
     */
    public function __construct(
        #[ExpectedValues(flagsFromClass: ResponseType::class)]
        public int $type = ResponseType::AUTO
    )
    {
    }
}