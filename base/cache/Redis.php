<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/6/30
 * Time: 下午5:14
 */

namespace base\cache;

use base\core\Config;

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
        if ($this->conn) {
            try {
                $this->close();
            } catch (\RedisException $e) {
                echo 'Caught exception in close Connection: '.$e->getMessage()."\n";
            } finally {
                $this->conn = null;
            }
        }
        $this->conn = new \Redis();
        $this->conn->pconnect($this->config['host'], 6379, 5);
        $this->conn->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_NONE);
        $this->conn->select(1);
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