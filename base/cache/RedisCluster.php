<?php

namespace base\cache;

use base\core\Config;
use base\log\Log;

class RedisCluster
{
    public static $instance = null;

    public function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new RedisCluster();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $config = Config::get('redis_cluster');
        $this->config['nodes'] = array();
        $nodes = $config['nodes'];
        foreach ($nodes as $node) {
            $this->config['nodes'][] = $node['host'] . ':' . $node['port'];
        }

        $this->config['connect_timeout'] = $config['connect_timeout'];
        $this->config['read_timeout']    = $config['read_timeout'];

        if (!isset($config['failover_mode'])) {
            $config['failover_mode'] = 'ERROR';
        }

        switch ($config['failover_mode']) {
            case 'NONE':
                $this->config['failover_mode']   = \RedisCluster::FAILOVER_NONE;
                break;
            case 'ERROR':
                $this->config['failover_mode']   = \RedisCluster::FAILOVER_ERROR;
                break;
            case 'DISTRIBUTE':
                $this->config['failover_mode']   = \RedisCluster::FAILOVER_DISTRIBUTE;
                break;
        }

        $this->_connect();
    }

    private function __destruct()
    {
        $this->_close();
    }

    private $conn;
    private $config;

    private function _connect()
    {
        try {
            $this->_close();
            $this->conn = new \RedisCluster(
                NULL,
                $this->config['nodes'],
                $this->config['connect_timeout'],
                $this->config['read_timeout']);

            $this->conn->setOption(\RedisCluster::OPT_SLAVE_FAILOVER, $this->config['failover_mode']);
        } catch (\RedisCluster $e) {
            Log::WARNING(__CLASS__, 'Exception(' . $e->getMessage() . ') occurs at ' . __FUNCTION__);
            $this->conn = null;
        }
    }

    private function _close() {
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

    public function getConnection() {
        return $this->conn;
    }
}
