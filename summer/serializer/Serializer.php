<?php


namespace summer\serializer;

/**
 * Interface Serializer
 * @package summer\serializer
 */
interface Serializer
{
    /**
     * 序列化
     * @param object $value 对象实例
     * @return string
     */
    public function serialize(object $value): string;

    /**
     * 反序列化
     * @param string $value 字符串
     * @param string $targetType
     * @return mixed
     */
    public function deserialize(string $value, string $targetType): object;
}