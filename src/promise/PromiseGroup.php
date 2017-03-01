<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/11/1
 * Time: 上午11:03
 */

namespace base\promise;

class PromiseGroup
{
    /**
     * @var int 已经添加的promise个数
     */
    private $count = 0;

    /**
     * @var int 剩余的promise个数
     */
    private $left = 0;

    /**
     * @var Promise 多合一回调
     */
    private $promise;

    /**
     * @var array Promise组
     */
    private $promises = [];

    /**
     * @var array Promise组的value数组
     */
    private $values = [];

    /**
     * @var array
     */
    private $executors = [];

    private $times = [];

    /**
     * PromiseGroup constructor.
     */
    public function __construct()
    {

    }

    public function setPromise(Promise $promise)
    {
        $this->promise = $promise;
    }

    /**
     * @param $key string
     * @param $run callable
     * @param null $callback callable
     */
    public function add($key, $run, $callback = null)
    {
        $promise = new Promise();
        $promise->then(function($value) use ($key, $callback) {
            if( is_callable($callback) )
            {
                $value = call_user_func($callback, true, $value);
            }
            
            $this->values[$key] = $value;
            $this->left --;
            if($this->left == 0)
            {
                $this->promise->resolve($this->values);
            }
        }, function($reason) use ($key, $callback) {
            if( is_callable($callback) )
            {
                $reason = call_user_func($callback, false, $reason);
            }
            $this->promise->resolve($reason);
        });
        $this->promises[$key] = $promise;
        $this->count ++;

        $this->executors[$key] = $run;
    }

    public function run()
    {
        $this->left = $this->count;
        foreach ($this->executors as $key => $executor)
        {
            $this->times[$key] = microtime(false);
            call_user_func($executor, $this->promises[$key]);
        }
    }
}