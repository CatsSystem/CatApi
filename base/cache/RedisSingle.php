<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/6/30
 * Time: 下午5:14
 */

namespace base\cache;

use base\core\Config;
use base\log\Log;


class RedisSingle
{
    public static $instance = null;

    public function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new RedisSingle();
        }
        return self::$instance;
    }

    private function __construct($config)
    {
        $this->config = Config::get('redis_single');
        $this->_connect();
    }

    public function __destruct()
    {
        $this->_close();
    }

    private $conn;
    private $config;

    private function _connect()
    {
        try {
            $this->_close();
            $this->conn = new \Redis();
            $this->conn->pconnect(
                $this->config['host'],
                $this->config['port'],
                5);
            $this->conn->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_NONE);
            $this->conn->select(1);
        } catch (\RedisException $e) {
            Log::WARNING(__CLASS__, 'Exception(' . $e->getMessage() . ') occurs at ' . __FUNCTION__);
            $this->conn = null;
        }
    }

    private function _close()
    {
        if ($this->conn) {
            try {
                $this->conn->close();
            } catch (\RedisException $e) {
                Log::WARNING(__CLASS__, 'Exception(' . $e->getMessage() . ') occurs at ' . __FUNCTION__);
            } finally {
                $this->conn = null;
            }
        }
    }

    public function getConnection()
    {
        return $this->conn;
    }
}
