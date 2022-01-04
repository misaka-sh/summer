<?php


namespace summer\route;


use Closure;
use ReflectionException;
use summer\Exception;
use summer\request\annotation\RequestHeader;
use summer\request\annotation\RequestMapping;
use summer\request\annotation\RequestMethod;
use summer\response\Response;
use summer\Tips;
use summer\utils\Json;

/**
 * 路由
 * @package summer\route
 */
class Route
{
    /**
     * 主机名
     * @var string
     */
    public string $host;
    /**
     * 名字 通常为:之前部分
     * @var string
     */
    public string $name;
    /**
     * 请求标识
     * @var string
     */
    public string $uri;
    /**
     * 路径
     * @var string
     */
    public string $path;
    /**
     * 请求方法
     * @var string
     */
    public string $method;
    /**
     * 请求头
     * @var array
     */
    public array $headers = [];
    /**
     * 远程地址
     * @var string
     */
    public string $remoteAddr;
    /**
     * 请求时间
     * @var int
     */
    public int $requestTime;
    /**
     * 请求时间浮点数 毫秒
     * @var float
     */
    public float $requestTimeFloat;

    /**
     * GET 参数
     * @var array
     */
    public array $get;
    /**
     * POST 参数
     * @var array
     */
    public array $post;
    /**
     * FILES 参数
     * @var array
     */
    public array $file;
    /**
     * INPUT 参数
     * @var string
     */
    public string $raw;

    //region CLI模式参数
    /**
     * 参数数量
     * @var int
     */
    public int $argc;
    /**
     * 参数集合
     * @var array
     */
    public array $argv;
    //endregion

    /**
     * 控制器集合
     * @var Controller[]
     */
    public array $controllers;

    /**
     * 通过url解析的变量集合
     * @var array
     */
    private array $variables = [];

    /**
     * Route constructor.
     * @throws ReflectionException|Exception
     */
    public function __construct()
    {
        switch (PHP_SAPI) {
            case "cli":
                $this->cli();
//                $this->cgi();
                break;
            case "fpm-fcgi":
            case "cgi-fcgi":
                $this->cgi();
                break;
        }
    }

    /**
     * CLI模式下
     */
    private function cli()
    {
        //region 属性赋值
        $this->argc = $_SERVER["argc"];
        $this->argv = $_SERVER["argv"];
        //endregion
    }

    /**
     * CGI模式
     * @throws ReflectionException|Exception
     */
    private function cgi()
    {
//        $_SERVER["HTTP_TICKET"] = "token";
//        $_SERVER["HTTP_ACCEPT_ENCODING"] = "gzip, deflate, br";
//        $_SERVER["HTTP_CONTENT_TYPE"] = "application/json";
        //region 属性赋值
        $this->host = $_SERVER["HTTP_HOST"];
        $this->name = $_SERVER["SERVER_NAME"];
        $this->uri = $this->path = $_SERVER["REQUEST_URI"];
        $this->method = $_SERVER["REQUEST_METHOD"];
        $this->remoteAddr = $_SERVER["REMOTE_ADDR"];
        $this->requestTime = $_SERVER["REQUEST_TIME"];
        $this->requestTimeFloat = $_SERVER["REQUEST_TIME_FLOAT"];


        foreach ($_SERVER as $name => $value) {
            if (str_starts_with($name, "HTTP_")) {
                $key = ucwords(str_replace("_", "-", strtolower(substr($name, 5))), "-");
                $this->headers[$key] = $value;
            }
        }
        //endregion
        $this->loadController();
        if (count($this->controllers)) $this->matchController($this->controllers);
    }

    /**
     * 加载控制器
     * @throws ReflectionException
     */
    private function loadController()
    {
        $filenames = scandir(ROOT_PATH . SRC_PATH . APP_PATH . CONTROLLER_PATH);
        $namespace = str_replace(DIRECTORY_SEPARATOR, "\\", APP_PATH . CONTROLLER_PATH);
        foreach ($filenames as $filename) {
            if (is_dir($filename)) continue;
            $filename = $namespace . basename($filename, EXTENSION);
            $this->controllers[] = new Controller($filename);
        }
    }

    /**
     * 匹配控制器
     * @param Controller[] $controllers 控制器
     * @throws Exception|ReflectionException
     */
    private function matchController(array $controllers)
    {
        //匹配控制器
        foreach ($controllers as $controller) {
            // 不是控制器 跳过
            if (!$controller->isController) continue;
            // 控制器存在请求映射
            if (!is_null($controller->requestMapping)) {
                // 控制器匹配到
                if ($this->matchControllerPath($controller->requestMapping)) {
                    if ($this->matchMethod($controller)) return;
                }
            } else {
                if ($this->matchMethod($controller)) return;
            }
        }
        throw new Exception(504, Tips::METHOD_NO_EXISTENT, $this->path);
    }

    /**
     * 匹配请求路径
     * @param RequestMapping $requestMapping
     * @return bool
     * @throws Exception
     */
    private function matchControllerPath(RequestMapping $requestMapping): bool
    {
        $result = false;
        /**
         * 规则
         * @param string $path
         * @return bool
         * @throws Exception
         */
        $rule = function (string $path): bool {
            if (strpos($path, "{") || strpos($path, "}")) throw new Exception(501, Tips::CONTROLLER_PATH, $path);
            $length = strlen($path);
            if (substr_compare($this->path, $path, 0, $length) === 0) {
                $this->path = substr($this->path, $length);
                return true;
            }
            return false;
        };
        $type = gettype($requestMapping->path);
        switch ($type) {
            case "string":
                $result = $rule($requestMapping->path);
                break;
            case "array":
                foreach ($requestMapping->path as $path) {
                    if ($result = $rule($path)) break;
                }
                break;
        }
        return $result;
    }

    /**
     * 匹配方法
     * @param Controller $controller
     * @return bool
     * @throws Exception
     * @throws ReflectionException
     */
    private function matchMethod(Controller $controller): bool
    {
        $result = false;
        $methods = $controller->methods;
        // 构造函数
        foreach ($methods as $method) {
            $name = $method->name;
            // 没有请求映射 跳过
            if (is_null($method->requestMapping)) continue;
            // 请求方法不匹配 跳过
            if ($this->method != $method->requestMapping->method) continue;
            // 匹配 方法失败 跳过
            if (!$this->matchMethodPath($method->requestMapping)) continue;
            new Response(
                $method->responseHeader,
                $method->responseBody,
                $method->getClosure($this->matchConstructor($controller)),
                $this->matchParameter($method->parameters)
            );
            $result = true;
            break;
        }
        $this->path = $this->uri;
        return $result;
    }

    /**
     * 匹配方法路径
     * @param RequestMapping $requestMapping
     * @return bool
     */
    private function matchMethodPath(RequestMapping $requestMapping): bool
    {
        $result = false;

        /**
         * 规则
         * @param string $path
         * @return bool
         */
        $rule = function (string $path): bool {
            $variables = explode("/", strstr($path, "{"));
            $path = strstr($path, "{", true) ?: $path;
            $length = strlen($path);
            if (substr_compare($this->path, $path, 0, $length) === 0) {
                $values = explode("/", substr($this->path, $length));
                for ($i = 0; $i < count($variables); $i++) {
                    $this->variables[trim($variables[$i], "{}")] = $values[$i];
                }
                return true;
            }
            return false;
        };

        $type = gettype($requestMapping->path);
        switch ($type) {
            case "string":
                $result = $rule($requestMapping->path);
                break;
            case "array":
                foreach ($requestMapping->path as $path) {
                    if ($result = $rule($path)) break;
                }
                break;
        }
        return $result;
    }

    /**
     * 匹配参数
     * @param Parameter[] $parameters 参数列表
     * @return array 参数
     * @throws Exception
     * @throws ReflectionException
     */
    private function matchParameter(array $parameters): array
    {

        foreach ($parameters as $parameter) {
            // 处理请求映射
            if (!is_null($parameter->requestHeader)) {
                $value = $this->parseRequestHeader($parameter->requestHeader);
                $parameter->value = $value;
                continue;
            }
            // 匹配路径变量
            if (isset($this->variables[$parameter->name])) {
                $parameter->value = $this->variables[$parameter->name];
                continue;
            }
            // 解析请求参数
            if ($this->parseParameter($parameter)) continue;
            // 存在默认值
            if ($parameter->isDefaultValueAvailable) {
                $parameter->value = $parameter->defaultValue;
                continue;
            }

            // 存在注解 不能为空
            if (!is_null($parameter->notEmpty)) {
                throw new Exception(501, Tips::NOT_EMPTY, $parameter->name, $parameter->notEmpty->message);
            }
        }
        $list = [];
        foreach ($parameters as $parameter) {
            if (gettype($parameter->value) == "object") $this->parameterValidate($parameter);
            $list[] = $parameter->value;
        }
        return $list;
    }

    /**
     * 匹配构造函数
     * @param Controller $controller 控制器
     * @return object
     * @throws Exception
     * @throws ReflectionException
     */
    private function matchConstructor(Controller $controller): object
    {
        $methods = $controller->methods;
        foreach (array_reverse($methods) as $method) {
            // 如果是构造函数 匹配参数 执行
            if ($method->isConstructor) {
                $parameters = $method->parameters;
                return $controller->instance->newInstanceArgs($this->matchParameter($parameters));
            }
        }
        return $controller->instance->newInstanceWithoutConstructor();
    }

    /**
     * 解析请求头映射
     * @param RequestHeader $requestHeader
     * @return mixed 成功返回数据 失败 返回 null 如果要头部存在
     * @throws Exception
     */
    private function parseRequestHeader(RequestHeader $requestHeader): mixed
    {

        $name = $requestHeader->name;
        if ($requestHeader->required) {
            if (!isset($this->headers[$name])) throw new Exception(501, Tips::REQUEST_HEADER, $name);
        }
        return $this->headers[$name] ?? $requestHeader->defaultValue;
    }

    /**
     * 解析参数
     * @param Parameter $parameter
     * @return bool
     * @throws Exception
     * @throws ReflectionException
     */
    private function parseParameter(Parameter $parameter): bool
    {
        return match ($this->method) {
            RequestMethod::GET, RequestMethod::DELETE => $this->parseGetParameter($parameter),
            RequestMethod::POST, RequestMethod::PUT => $this->parsePostParameter($parameter),
            default => false,
        };
    }

    /**
     * 解析 GET 参数
     * @param Parameter $parameter
     * @return bool
     */
    private function parseGetParameter(Parameter $parameter): bool
    {
        $parameter->value = $_GET[$parameter->name] ?? false;
        if (!$parameter->value) return false;
        return true;
    }

    /**
     * 解析POST参数
     * @param Parameter $parameter
     * @return bool
     * @throws Exception
     * @throws ReflectionException
     */
    private function parsePostParameter(Parameter $parameter): bool
    {
        $type = $this->headers["Content-Type"] ?? "";
        if (str_contains($type, "application/x-www-form-urlencoded") || str_contains($type, "multipart/form-data")) {
            $parameter->value = $_POST[$parameter->name] ?? false;
            if (!$parameter->value) return false;
            return true;
        }
        // json 反序列化 映射
        if (str_contains($type, "application/json")) {
            return $this->parseJsonParameter($parameter);
        }
        return false;
    }

    /**
     * 解析JSON字符串 映射赋值
     * @param Parameter $parameter
     * @return bool
     * @throws Exception
     * @throws ReflectionException
     */
    private function parseJsonParameter(Parameter $parameter): bool
    {
        $input = file_get_contents("php://input");
        $parameter->value = Json::deserialize($input, $parameter->type);
        return true;
    }

    /**
     * 参数验证器
     * @param Parameter $parameter
     * @throws Exception
     */
    private function parameterValidate(Parameter $parameter)
    {
        $validate = new Validate($parameter->value);
        foreach ($validate->property as $property) {
            if (!$property->isInitialized($parameter->value)) {
                if (!is_null($property->notEmpty)) throw new Exception(501, Tips::NOT_EMPTY, $property->name, $property->notEmpty->message);
            }
        }
    }
}