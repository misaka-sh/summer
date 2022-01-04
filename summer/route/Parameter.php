<?php


namespace summer\route;

use ReflectionAttribute;
use ReflectionException;
use ReflectionParameter;
use ReflectionType;
use summer\request\annotation\RequestHeader;
use summer\request\constraints\NotEmpty;

/**
 * 参数
 * @package summer\route
 */
class Parameter
{
    /**
     * 参数名
     * @var string
     */
    public string $name;
    /**
     * 值
     * @var mixed
     */
    public mixed $value;
    /**
     * 默认值没有的时候为 null
     * @var mixed
     */
    public mixed $defaultValue;
    /**
     * 是否有默认值
     * @var bool
     */
    public bool $isDefaultValueAvailable = false;
    /**
     * 类型
     * @var string|null
     */
    public ?string $type = null;

    /**
     * 请求字段头部映射
     * @var RequestHeader|null
     */
    public ?RequestHeader $requestHeader = null;
    /**
     * 不为空
     * @var NotEmpty|null
     */
    public ?NotEmpty $notEmpty = null;


    /**
     * Parameter constructor.
     * @param ReflectionParameter $parameter
     * @throws ReflectionException
     */
    public function __construct(ReflectionParameter $parameter)
    {
        $this->name = $parameter->name;
        if ($parameter->isDefaultValueAvailable()) {
            $this->isDefaultValueAvailable = true;
            $this->defaultValue = $parameter->getDefaultValue();
        }
        if ($parameter->hasType()) $this->type = $parameter->getType();


        $attributes = $parameter->getAttributes();
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
                case RequestHeader::class:
                    $this->requestHeader = $attribute->newInstance();
                    break;
                case NotEmpty::class:
                    $this->notEmpty = $attribute->newInstance();
                    break;
            }
        }
    }
}