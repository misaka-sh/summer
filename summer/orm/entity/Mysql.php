<?php


namespace summer\orm\entity;

/**
 * Mysql 配置
 * @package summer\orm\entity
 */
class Mysql
{
    /**
     * 主机名
     * @var string
     */
    public string $hostname;
    /**
     * MySql 用户名
     * @var string
     */
    public string $username;
    /**
     * 密码
     * @var string
     */
    public string $password;
    /**
     * 数据库
     * @var string
     */
    public string $database;
    /**
     * 端口号
     * @var int
     */
    public int $port;
}