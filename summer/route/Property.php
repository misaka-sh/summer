<?php


namespace summer\route;


use JetBrains\PhpStorm\Pure;
use ReflectionAttribute;
use ReflectionProperty;
use summer\request\constraints\NotEmpty;
use summer\request\constraints\NotNull;

/**
 * 属性
 * @package summer\route
 */
class Property
{
    /**
     * 属性名
     * @var string
     */
    public string $name;
    /**
     * 映射属性实例
     * @var ReflectionProperty|null
     */
    private ?ReflectionProperty $instance = null;
    /**
     * 不能为空
     * @var NotEmpty|null
     */
    public ?NotEmpty $notEmpty = null;
    /**
     * 不能为null
     * @var NotNull|null
     */
    public ?NotNull $notNull = null;

    /**
     * Property constructor.
     * @param ReflectionProperty $property
     */
    public function __construct(ReflectionProperty $property)
    {
        $this->name = $property->name;
        $this->instance = $property;
        $attributes = $property->getAttributes();
        if (count($attributes)) $this->parseAttribute($attributes);
    }

    /**
     * 解析注解
     * @param ReflectionAttribute[] $attributes
     */
    private function parseAttribute(array $attributes)
    {
        foreach ($attributes as $attribute) {
            $name = $attribute->getName();
            switch ($name) {
                case NotEmpty::class:
                    $this->notEmpty = $attribute->newInstance();
                    break;
                case NotNull::class:
                    $this->notNull = $attribute->newInstance();
                    break;
            }
        }
    }

    /**
     * 判断属性是否初始化
     * @param object $object 对象实例
     * @return bool
     */
    #[Pure] public function isInitialized(object $object): bool
    {
        return $this->instance->isInitialized($object);
    }
}