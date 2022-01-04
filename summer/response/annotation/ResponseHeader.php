<?php


namespace summer\response\annotation;

use Attribute;

/**
 * 响应头
 * @package summer\response\annotation\
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class ResponseHeader
{
    /**
     * ResponseHeader constructor.
     * @param array $headers ["content-type: text/html; charset=utf-8"]
     */
    public function __construct(
        public array $headers = []
    )
    {
    }
}