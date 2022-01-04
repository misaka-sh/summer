<?php


namespace summer\serializer\json;


use ReflectionProperty;

/**
 * 属性
 * @package summer\serializer\json
 */
class Property
{
    /**
     * 属性名
     * @var string
     */
    public string $name;
    /**
     * 值
     * @var mixed
     */
    public mixed $value;
    /**
     * 映射属性实例
     * @var ReflectionProperty
     */
    private ReflectionProperty $property;
    /**
     * 实例
     * @var object
     */
    private object $instance;
    /**
     * 类型
     * @var array
     */
    public array $type = [];
    /**
     * 类
     * @var string
     */
    public string $class;

    /**
     * Property constructor.
     * @param ReflectionProperty $property 属性映射
     * @param object $instance 类实例
     */
    public function __construct(ReflectionProperty $property, object $instance)
    {
        $this->name = $property->name;
        $this->property = $property;
        $this->instance = $instance;
        $this->parseType($property);
        if ($property->isInitialized($instance)) $this->value = $property->getValue($instance);

    }


    /**
     * 设置属性值
     * @param mixed $value
     */
    public function setValue(mixed $value): void
    {
        $this->property->setValue($this->instance, $value);
    }

    /**
     * 设置类型
     * @param ReflectionProperty $property
     */
    private function parseType(ReflectionProperty $property)
    {
        if ($property->hasType()) {
            $type = $property->getType();
            // null简洁声明 ?只能在开头
            if (str_starts_with($type, "?")) {
                $this->type[] = "null";
                $type = substr($type, 1);
            }
            $type = str_replace(["int", "float"], ["integer", "double"], $type);
            foreach (explode("|", $type) as $value) {
                // 如果出现反斜杠 必定是命名空间
                if (str_contains($value, "\\")) {
                    $this->type[] = "object";
                    $this->class = $value;
                    continue;
                }
                $this->type[] = $value;
            }
        } else {
            $this->type[] = gettype($property->getValue($this->instance));
        }
    }

}