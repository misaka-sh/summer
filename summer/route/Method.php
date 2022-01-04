<?php


namespace summer\route;


use Closure;
use JetBrains\PhpStorm\Pure;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use summer\request\annotation\RequestMapping;
use summer\response\annotation\ResponseBody;
use summer\response\annotation\ResponseHeader;

/**
 * 方法
 * @package summer\route
 */
class Method
{
    /**
     * 方法名
     * @var string
     */
    public string $name;
    /**
     * 映射方法类实例
     * @var ReflectionMethod|null
     */
    private ?ReflectionMethod $instance = null;
    /**
     * 方法修饰符
     *
     * ReflectionMethod modifiers:
     *
     *  - {@see ReflectionMethod::IS_STATIC} - 静态方法
     *  - {@see ReflectionMethod::IS_PUBLIC} - 公开方法.
     *  - {@see ReflectionMethod::IS_PROTECTED} - 受保护方法.
     *  - {@see ReflectionMethod::IS_PRIVATE} - 私有方法
     *  - {@see ReflectionMethod::IS_ABSTRACT} - 抽象方法
     *  - {@see ReflectionMethod::IS_FINAL} - 最终方法
     * @var int
     */
    public int $modifiers;
    /**
     * 是否构造函数
     * @var bool
     */
    public bool $isConstructor;
    /**
     * 是否析构函数
     * @var bool
     */
    public bool $isDestructor;
    /**
     * 请求映射
     * @var RequestMapping|null
     */
    public ?RequestMapping $requestMapping = null;
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
     * 参数
     * @var Parameter[]
     */
    public array $parameters = [];

    /**
     * Method constructor.
     * @param ReflectionMethod $method
     * @throws ReflectionException
     */
    public function __construct(ReflectionMethod $method)
    {
        $this->name = $method->name;
        $this->instance = $method;
        $this->modifiers = $method->getModifiers();
        $this->isConstructor = $method->isConstructor();
        $this->isDestructor = $method->isDestructor();
        //解析注解
        $attributes = $method->getAttributes();
        if (count($attributes)) $this->parseAttribute($attributes);
        //解析参数
        $parameters = $method->getParameters();
        if (count($parameters)) $this->parseParameter($parameters);
    }

    /**
     * 创建方法闭包
     * @param object $instance
     * @return Closure
     */
    #[Pure] public function getClosure(object $instance): Closure
    {
        return $this->instance->getClosure($instance);
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
                case RequestMapping::class:
                    $this->requestMapping = $attribute->newInstance();
                    break;
                case ResponseHeader::class:
                    $this->responseHeader = $attribute->newInstance();
                    break;
                case ResponseBody::class:
                    $this->responseBody = $attribute->newInstance();
                    break;
            }

        }
    }

    /**
     * 解析参数
     * @param ReflectionParameter[] $parameters
     * @throws ReflectionException
     */
    private function parseParameter(array $parameters)
    {
        foreach ($parameters as $parameter) {
            $this->parameters[] = new Parameter($parameter);
        }
    }


}