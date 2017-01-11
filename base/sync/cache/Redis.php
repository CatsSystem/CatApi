<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/4/11
 * Time: 下午4:17
 */

namespace base\sync\cache;

use base\config\Config;

class Redis
{
    private static $instance = null;

    /**
     * @return Redis
     *
     */
    public static function getInstance()
    {
        if(self::$instance == null )
        {
            self::$instance = new Redis(Config::get('redis'));
        }
        return self::$instance;
    }

    private $conn;
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
        $this->connect();
    }

    public function connect()
    {
        $timeOut = 0;
        if(isset($this->config['timeout'])) {
            $timeOut = $this->config['timeout'];
        }

        $this->conn = new \Redis();
        $this->conn->connect($this->config['host'], $this->config['port'], $timeOut);
        if( isset($this->config['pwd']) ) {
            $this->conn->auth($this->config['pwd']);
        }
        $this->conn->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_NONE);
        $this->conn->select($this->config['select']);
    }

    /**
     * @return \Redis
     */
    public function getConnection()
    {
        return $this->conn;
    }

    public function close()
    {
        $this->conn->close();
    }

}