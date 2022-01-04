<?php


namespace summer;


use JetBrains\PhpStorm\Pure;
use ReflectionClass;
use ReflectionException;
use summer\response\Response;
use summer\route\Route;
use Throwable;

/**
 * 框架主体封装
 * @package summer
 */
class Summer
{
    /**
     * 版本
     */
    const VERSION = "1.0.0";
    /**
     * 应用来源
     * @var string
     */
    public string $primarySource;
    /**
     * 应用
     * @var Application
     */
    public Application $application;
    /**
     * 路由
     * @var Route
     */
    public Route $route;

    /**
     * 框架启动
     * @param string $primarySource 应用来源
     * @return Summer
     */
    public static function run(string $primarySource): Summer
    {
        return new Summer($primarySource);
    }


    /**
     * Summer constructor.
     * @param string $primarySource 应用来源
     */
    public function __construct(string $primarySource)
    {
        $this->primarySource = $primarySource;
        $this->init();
    }

    /**
     * 初始化 框架自身模块
     */
    private function init()
    {
        try {
            $this->application = new Application($this->primarySource);
            $this->route = new Route();

        } catch (Throwable $e) {
            $this->abnormal($e->getMessage(), $e->getCode());
        }
    }

    /**
     * 处理异常
     * @param string $message 错误信息
     * @param int $code 错误代码
     */
    private function abnormal(string $message, int $code)
    {
        if (!is_null($this->application->abnormal)) {
            new Response(
                $this->application->responseHeader,
                $this->application->responseBody,
                $this->application->abnormal,
                [$message, $code]
            );
        }
    }
}