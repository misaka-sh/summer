<?php


namespace summer\response\annotation;

/**
 * 响应类型
 * @package summer\response\annotation
 */
class ResponseType
{
    /**
     * 根据类型不同 自动调整
     */
    const AUTO = 0x00;
    const TEXT = 0x01;
    /**
     * @deprecated 未实现
     */
    const XML = 0x02;
    const JSON = 0x03;
}