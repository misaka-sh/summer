<?php


namespace summer;


use Closure;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use summer\annotation\SummerApplication;
use summer\response\annotation\ResponseBody;
use summer\response\annotation\ResponseHeader;

/**
 * 应用
 * @package summer
 */
class Application
{
    /**
     * 应用名
     * @var string
     */
    public string $name;
    /**
     * 类实例
     * @var object|null
     */
    public ?object $instance = null;
    /**
     * 是否应用类
     * @var bool
     */
    public bool $isApplication = false;
    /**
     * 响应头
     * @var ResponseHeader|null
     */
    public ?ResponseHeader $responseHeader = null;
    /**
     * 响应体
     * @var ResponseBody|null
     */
    public ?ResponseBody $responseBody = null;

    /**
     * 处理异常函数
     * @var Closure|null
     */
    public ?Closure $abnormal = null;

    /**
     * Application constructor.
     * @param string $application
     * @throws ReflectionException
     */
    public function __construct(string $application)
    {
        $this->name = $application;
        $this->parseClass($application);
    }

    /**
     * 解析类
     * @param string $objectOrClass
     * @throws ReflectionException
     */
    private function parseClass(string $objectOrClass)
    {
        $class = new ReflectionClass($objectOrClass);
        $attributes = $class->getAttributes();
        if (count($attributes)) $this->parseAttribute($attributes);
        if ($this->isApplication) {
            $this->instance = $class->newInstanceWithoutConstructor();
            $methods = $class->getMethods();
            if (count($methods)) $this->parseMethod($methods);
        }
    }

    /**
     * 解析注解
     * @param ReflectionAttribute[] $attributes
     */
    private function parseAttribute(array $attributes): void
    {
        foreach ($attributes as $attribute) {
            $name = $attribute->getName();
            if ($name == "Attribute") continue;

            switch ($name) {
                case SummerApplication::class:
                    $this->isApplication = true;
                    break;
                case ResponseHeader::class:
                    $this->responseHeader = $attribute->newInstance();
                    break;
                case ResponseBody::class:
                    $this->responseBody = $attribute->newInstance();
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * 解析方法
     * @param ReflectionMethod[] $methods
     */
    private function parseMethod(array $methods)
    {
        foreach ($methods as $method) {
            if ($method->getModifiers() == ReflectionMethod::IS_PRIVATE) {
                $name = $method->name;
                switch ($name) {
                    case "abnormal":
                        $this->abnormal = $method->getClosure($this->instance);
                        break;
                    default:
                        break;
                }
            }
        }
    }
}