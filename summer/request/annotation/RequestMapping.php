<?php


namespace summer\request\annotation;

use Attribute;
use JetBrains\PhpStorm\ExpectedValues;

/**
 * 请求映射
 * @package summer\request\annotation
 */
#[Attribute]
class RequestMapping
{
    /**
     * 路径映射 URI
     * @var string[]
     */
    public string|array $path;
    /**
     * 请求方法
     * @var RequestMethod[]
     */
    public string|array $method;
    /**
     * 映射请求的标头
     * @var string[]
     */
    public string|array $headers;

    /**
     * 请求映射
     * @param string[] $path
     * @param RequestMethod[] $method
     * @param string[] $headers
     */
    public function __construct(string|array $path = [], #[ExpectedValues(flagsFromClass: RequestMethod::class)] string|array $method = [], string|array $headers = [])
    {
        $this->path = $path;
        $this->method = $method;
        $this->headers = $headers;
    }
}