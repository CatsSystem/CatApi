<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/10/28
 * Time: 下午5:46
 */

namespace base\async\cache;

use base\config\Config;
use base\promise\Promise;
use base\common\Error;

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
        $this->config = Config::get('async_redis');

        //TODO Cluster
    }

    public function connect(Promise $promise)
    {
        if($this->is_close)
        {
            $this->redis = new \swoole_redis();

            $this->redis->on("close", function(){
                $this->is_close = true;
                $this->connect(new Promise());
            });
            $timeId = swoole_timer_after(3000, function() use ($promise){
                $this->close();
                $promise->resolve([
                    'code'  => Error::ERR_REDIS_TIMEOUT
                ]);
            });
            $this->redis->connect($this->config['host'], $this->config['port'],
                function (\swoole_redis $client, $result) use($timeId,$promise){
                    \swoole_timer_clear($timeId);
                    $this->is_close = false;
                    if( isset($this->config['pwd']) ) {
                        $client->auth($this->config['pwd'], function(\swoole_redis $client, $result) use ($promise){
                            if( !$result ) {
                                $this->close();
                                $promise->resolve([
                                    'code'  => Error::ERR_REDIS_ERROR,
                                    'data'  => $result
                                ]);
                                return;
                            }
                            $client->select($this->config['select'], function(\swoole_redis $client, $result){});
                            $promise->resolve([
                                'code'  => Error::SUCCESS
                            ]);
                        });
                    } else {
                        $client->select($this->config['select'], function(\swoole_redis $client, $result){});
                        $promise->resolve([
                            'code'  => Error::SUCCESS
                        ]);
                    }
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
        $timeId = swoole_timer_after(3000, function() use ($promise){
            $this->close();
            $promise->resolve([
                'code'  => Error::ERR_REDIS_TIMEOUT
            ]);
        });
        $arguments[$index] = function (\swoole_redis $client, $result) use ($timeId, $promise){
            \swoole_timer_clear($timeId);
            if( $result === false )
            {
                $promise->resolve([
                    'code'  => Error::ERR_REDIS_ERROR
                ]);
                return;
            }
            $promise->resolve([
                'code'  => Error::SUCCESS,
                'data'  => $result
            ]);
        };
        call_user_func_array([$this->redis, $name], $arguments);
    }

}