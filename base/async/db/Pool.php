<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/11/1
 * Time: 下午6:41
 */

namespace base\async\db;


use base\promise\Promise;
use base\config\Config;

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

    private $task_queue;
    
    public function __construct()
    {
        $this->queue        = new \SplQueue();
        $this->task_queue   = new \SplQueue();
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
        $promise->then(function() use ($driver){
            $this->queue->enqueue($driver);
            $this->count ++;
            if($this->count == $this->total && is_callable($this->callback))
            {
                call_user_func($this->callback);
            }
            if (count($this->task_queue) > 0)
            {
                $this->doTask();
            }
        }, function() use ($id) {
            $this->new_connect($id);
        });
        $driver->connect(false, $promise);
    }

    /**
     * @param $sql
     * @param $promise
     * @param $timeout
     * @return Driver|null
     */
    public function get($sql, $promise, $timeout)
    {
        while ( !$this->queue->isEmpty() )
        {
            $driver = $this->queue->dequeue();
            if( $driver->isClose() )
            {
                continue;
            }
            return $driver;
        }
        $this->task_queue->enqueue([$sql, $promise, $timeout]);
        return null;
    }

    /**
     * @param $driver Driver
     * @param bool $is_close
     */
    public function close($driver, $is_close = false)
    {
        if( $is_close ) {
            $this->new_connect($driver->getId());
            return;
        }
        $this->queue->enqueue($driver);
        if (count($this->task_queue) > 0)
        {
            $this->doTask();
        }
    }

    private function doTask()
    {
        $task = $this->task_queue->dequeue();
        $driver = $this->get($task[0], $task[1], $task[2]);
        $driver->async_query($task[0], $task[1], $task[2]);
    }

}