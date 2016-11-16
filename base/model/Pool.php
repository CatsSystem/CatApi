<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/11/1
 * Time: 下午6:41
 */

namespace base\model;


use GuzzleHttp\Promise\Promise;
use sdk\config\Config;

class Pool
{
    private static $instance = null;

    /**
     * @return Pool
     */
    public static function getInstance()
    {
        if(Pool::$instance == null)
        {
            Pool::$instance = new Pool();
        }
        return Pool::$instance;
    }
    
    private $queue;
    private $count;
    private $total;
    private $callback;
    
    public function __construct()
    {
        $this->queue = new \SplQueue();
    }

    public function init($callback = null, $count = 0)
    {
        if( $count == 0 )
        {
            $count = Config::get('mysql_pool_count');
        }
        $this->total = $count;
        $this->count = 0;
        while ($count > 0)
        {
            $this->new_connect($count);
            $count --;
        }
        $this->callback = $callback;
    }

    private function new_connect($id) {
        $driver = Driver::newInstance("SwooleMySQL", $id);
        $promise = new Promise();
        $promise->then(function($value) use ($driver){
            $this->queue->enqueue($driver);
            $this->count ++;
            if($this->count == $this->total && is_callable($this->callback))
            {
                call_user_func($this->callback);
            }
        }, function($reason) use ($id) {
            var_dump($reason);
            $this->new_connect($id);
        });
        $driver->connect(false, $promise);
    }

    /**
     * @return Driver
     */
    public function get()
    {
        if( $this->queue->isEmpty() )
        {
            return null;
        }
        return $this->queue->dequeue();
    }

    public function close($driver)
    {
        $this->queue->enqueue($driver);
    }

}