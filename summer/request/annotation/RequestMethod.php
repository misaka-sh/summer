<?php


namespace summer\request\annotation;

/**
 * 请求方法
 * @package summer\request\annotation
 */
class RequestMethod
{
    const GET = "GET";
    const HEAD = "HEAD";
    const POST = "POST";
    const PUT = "PUT";
    const PATCH = "PATCH";
    const DELETE = "DELETE";
    const OPTIONS = "OPTIONS";
    const TRACE = "TRACE";
}