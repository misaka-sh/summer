<?php


namespace summer\route;


use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use summer\request\annotation\RequestMapping;
use summer\response\annotation\ResponseBody;

/**
 * 控制器
 * @package summer\route
 */
class Controller
{
    /**
     * 控制器名
     * @var string
     */
    public string $name;
    /**
     * 映射类实例
     * @var ReflectionClass|null
     */
    public ReflectionClass|null $instance = null;
    /**
     * 是否控制器
     * @var bool
     */
    public bool $isController = false;

    /**
     * 请求映射
     * @var RequestMapping|null
     */
    public ?RequestMapping $requestMapping = null;
    /**
     * 响应体
     * @var ResponseBody|null
     */
    public ?ResponseBody $responseBody = null;

    /**
     * 方法
     * @var Method[]
     */
    public array $methods = [];
    /**
     * 匹配到的方法
     * @var Method|null
     */
    public ?Method $method = null;

    /**
     * Controller constructor.
     * @param string $controller
     * @throws ReflectionException
     */
    public function __construct(string $controller)
    {
        $this->name = $controller;
        $this->parseClass($controller);

    }

    /**
     * 解析注解
     * @param string $objectOrClass
     * @throws ReflectionException
     */
    private function parseClass(string $objectOrClass)
    {
        $class = new ReflectionClass($objectOrClass);
        //解析注解
        $attributes = $class->getAttributes();

        if (count($attributes)) $this->parseAttribute($attributes);
        //解析方法
        if ($this->name == $class->name && $this->isController) {
            $this->instance = $class;
            $this->parseMethod($class->getMethods());
        }

    }

    /**
     * 解析注解
     * @param ReflectionAttribute[] $attributes
     * @throws ReflectionException
     */
    private function parseAttribute(array $attributes)
    {
        foreach ($attributes as $attribute) {
            $name = $attribute->getName();
            if ($name == "Attribute") continue;
            switch ($name) {
                case \summer\annotation\Controller::class:
                    $this->isController = true;
                    break;
                case RequestMapping::class:
                    $this->requestMapping = $attribute->newInstance();
                    break;
                case ResponseBody::class:
                    $this->responseBody = $attribute->newInstance();
            }
            $this->parseClass($name);
        }

    }

    /**
     * 解析方法
     * @param ReflectionMethod[] $methods
     * @throws ReflectionException
     */
    private function parseMethod(array $methods)
    {
        foreach ($methods as $method) {
            $this->methods[] = new Method($method);
        }
    }
}