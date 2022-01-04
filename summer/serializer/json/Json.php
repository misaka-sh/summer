<?php


namespace summer\serializer\json;


use ReflectionClass;
use ReflectionException;
use ReflectionObject;
use ReflectionProperty;
use summer\Exception;
use summer\serializer\Serializer;
use summer\Tips;

/**
 * Json 序列化 反序列化 具体的实现
 * @package summer\serializer\json
 */
class Json implements Serializer
{
    /**
     * 将指定类型的值转换为 JSON 字符串
     * @param object $value 对象
     * @return string
     */
    public function serialize(object $value): string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    /**
     * JSON字符串 编码文本分析为指定类型的实例。
     * @param string $value 字符串
     * @param string $targetType 目标类型
     * @return object
     * @throws Exception
     * @throws ReflectionException
     */
    public function deserialize(string $value, string $targetType): object
    {
        $object = json_decode($value);
        if (json_last_error() !== 0) {
            throw new Exception(501, Tips::SERIALIZER_JSON, json_last_error_msg());
        }

        $target = $this->parseClass($targetType);
        $this->matchProperty($object, $target);
        return $target;
    }

    /**
     * 匹配对象
     * @param object $object
     * @param object $target
     * @throws ReflectionException
     */
    private function matchProperty(object $object, object $target)
    {
        $objects = $this->parseObject($object);
        $targets = $this->parseObject($target);;

        foreach ($targets as $target) {
            if (!$property = $objects[$target->name] ?? false) continue;
            //交集 理论上 只会匹配一个类型
            $intersect = array_intersect($property->type, $target->type);
            // 没有交集的 跳出
            if (!count($intersect)) continue;
            // 数组类型
            if (in_array("array", $intersect)) {
                $list = [];
                foreach ($property->value as $value) {
                    //精确匹配
                    if (gettype($value) == "object") {
                        $child = $this->parseClass($target->class);
                        $this->matchProperty($value, $child);
                        $list[] = $child;
                        continue;
                    }
                    // 类型模糊不匹配 跳出
                    if (!in_array(gettype($value), $target->type)) continue;
                    $list[] = $value;
                }
                $target->setValue($list);
                continue;
            }
            if (in_array("object", $intersect)) {
                $child = $this->parseClass($target->class);
                $this->matchProperty($property->value, $child);
                $target->setValue($child);
                continue;
            }
            $target->setValue($property->value);
        }
    }

    /**
     * 解析类
     * @param string|object $objectOrClass 对象
     * @return object
     * @throws ReflectionException
     */
    private function parseClass(string|object $objectOrClass): object
    {
        $class = new ReflectionClass($objectOrClass);
        return $class->newInstanceWithoutConstructor();
    }

    /**
     * 解析对象
     * @param object $instance 对象实例
     * @return Property[]
     */
    private function parseObject(object $instance): array
    {
        $object = new ReflectionObject($instance);
        $properties = $object->getProperties(ReflectionProperty::IS_PUBLIC);
        return $this->parseClassProperties($properties, $instance);
    }

    /**
     * 解析属性
     * @param ReflectionProperty[] $properties 属性数组
     * @param object $instance 当前对象实例
     * @return Property[]
     */
    private function parseClassProperties(array $properties, object $instance): array
    {
        $list = [];
        foreach ($properties as $property) {
            $list[$property->name] = new Property($property, $instance);
        }
        return $list;
    }
}