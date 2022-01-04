<?php


namespace summer;

use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\Pure;
use Throwable;

/**
 * 框架异常
 * @package summer
 */
class Exception extends \Exception
{
    /**
     * Exception constructor.
     * @param int $code 错误代码
     * @param string $format 格式
     * @param mixed ...$values 值
     */
    #[Pure] public function __construct($code, #[ExpectedValues(flagsFromClass: Tips::class)] string $format, ...$values)
    {
        array_unshift($values, $format);
        parent::__construct(call_user_func_array("sprintf", $values), $code);
    }
}