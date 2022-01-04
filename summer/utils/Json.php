<?php


namespace summer\utils;

use JetBrains\PhpStorm\Pure;
use ReflectionException;
use summer\Exception;
use summer\serializer\json\Json as JsonSerializer;

/**
 * JSON 工具类
 * @package summer\utils
 */
class Json
{
    /**
     * 将指定类型的值转换为 JSON 字符串
     * @param object $value 对象
     * @return string
     */
    public static function serialize(object $value): string
    {
        $serializer = new JsonSerializer();
        return $serializer->serialize($value);
    }

    /**
     * JSON字符串 编码文本分析为指定类型的实例。
     * @param string $value 字符串
     * @param string $targetType 目标对象
     * @return object
     * @throws ReflectionException
     * @throws Exception
     */
    public static function deserialize(string $value, string $targetType): object
    {
        $serializer = new JsonSerializer();
        return $serializer->deserialize($value, $targetType);
    }
}