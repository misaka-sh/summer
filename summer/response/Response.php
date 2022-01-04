<?php


namespace summer\response;


use Closure;
use summer\response\annotation\ResponseBody;
use summer\response\annotation\ResponseHeader;
use summer\response\annotation\ResponseType;

/**
 * 响应
 * @package summer\response
 */
class Response
{
    /**
     * 响应头
     * @var ResponseHeader|null
     */
    public ?ResponseHeader $responseHeader;
    /**
     * 响应体
     * @var ResponseBody|null
     */
    public ?ResponseBody $responseBody;
    /**
     * 数据
     * @var mixed
     */
    public mixed $data;

    /**
     * Response constructor.
     * @param ResponseHeader|null $responseHeader 响应头
     * @param ResponseBody|null $responseBody 响应体
     * @param Closure|null $closure 函数
     * @param array $args 参数
     */
    public function __construct(ResponseHeader $responseHeader = null, ResponseBody $responseBody = null, Closure $closure = null, array $args = [])
    {
        $this->responseHeader = $responseHeader;
        $this->responseBody = $responseBody;
        $this->data = call_user_func_array($closure, $args);
        $this->init();
    }

    /**
     *
     */
    private function init(): void
    {
        $result = match ($this->responseBody?->type) {
            ResponseType::TEXT => $this->responseText(),
            ResponseType::XML => $this->responseXml(),
            ResponseType::JSON => $this->responseJson(),
            default => $this->responseAuto(),
        };
        $this->responseHeader();
        echo $result;
        return;
    }

    /**
     * 响应头
     */
    private function responseHeader(): void
    {
        if (!is_null($this->responseHeader)) {
            foreach ($this->responseHeader->headers as $header) {
                header($header);
            }
        }
    }

    /**
     * 响应自动
     * @return bool|string
     */
    private function responseAuto(): bool|string
    {
        return match (gettype($this->data)) {
            "string", "integer", "double", "boolean", "NULL" => $this->responseText(),
            "object", "array" => $this->responseJson(),
        };
    }

    /**
     * 响应文本
     * @return string
     */
    private function responseText(): string
    {
        return $this->data;
    }

    /**
     * @return string
     * @deprecated 未实现
     */
    private function responseXml(): string
    {
        return "xml";
    }

    /**
     * 响应 json 字符串
     * @return bool|string
     */
    private function responseJson(): bool|string
    {
        return json_encode($this->data, JSON_UNESCAPED_UNICODE);
    }

}