<?php


namespace summer\lang;

/**
 * 中文简体提示
 * @package summer\lang
 */
class Zh_CN
{
    /**
     * 控制器路径 不能存在变量
     */
    const CONTROLLER_PATH = "控制器路径 不能存在 {} 字符: %s";
    /**
     * 请求映射请求头 失败提示
     */
    const REQUEST_HEADER = "缺少请求头参数: %s ";

    /**
     * 方法不存在
     */
    const METHOD_NO_EXISTENT = "API不存在: %s ";

    /**
     * 参数不能为空
     */
    const NOT_EMPTY = "%s %s";

    /**
     * json映射 解析异常
     */
    const SERIALIZER_JSON = "处理json字符串 反序列化异常: %s";
}