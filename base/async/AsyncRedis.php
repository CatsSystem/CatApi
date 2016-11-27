<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/10/28
 * Time: 下午5:46
 */

namespace base\async;


use base\core\Config;
use GuzzleHttp\Promise\Promise;

class AsyncRedis
{
    private static $instance = null;

    /**
     * @return AsyncRedis
     */
    public static function getInstance()
    {
        if(AsyncRedis::$instance == null)
        {
            AsyncRedis::$instance = new AsyncRedis();
        }
        return AsyncRedis::$instance;
    }

    /**
     * @var \swoole_redis
     */
    private $redis;

    private $is_close = true;

    private $config;

    public function __construct()
    {
        $this->config = Config::get('redis');
    }

    public function connect(Promise $promise)
    {
        if($this->is_close)
        {
            $this->redis = new \swoole_redis();

            $this->redis->on("close", function($redis){
                $this->is_close = true;
                $this->connect(new Promise());
            });
            $timeId = swoole_timer_after(3000, function() use ($promise){
                $this->close();
                $promise->resolve(-1);
            });
            $this->redis->connect($this->config['host'], $this->config['port'],
                function (\swoole_redis $client, $result) use($timeId,$promise){
                    \swoole_timer_clear($timeId);
                    $this->is_close = false;
                    $promise->resolve($result);
            });
        }
    }

    public function close()
    {
        $this->redis->close();
        $this->is_close = true;
    }

    public function __call($name, $arguments)
    {
        $index = count($arguments) - 1;
        $promise = $arguments[$index];
        if( ! $promise instanceof Promise )
        {
            return false;
        }
        $arguments[$index] = function (\swoole_redis $client, $result) use ($promise){
            $promise->resolve($result);
        };
        call_user_func_array([$this->redis, $name], $arguments);
    }

}