<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/10/26
 * Time: 下午6:25
 */

namespace base\model;

use GuzzleHttp\Promise\Promise;

class AsyncModel
{
    private $table;

    public function __construct($table)
    {
        $this->table = $table;
    }

    public function query($sql, Promise $promise)
    {
        $driver = Pool::getInstance()->get();
        if(empty($driver))
        {
            swoole_timer_tick(1, function($timer_id) use ($sql, $promise){
                $driver = Pool::getInstance()->get();
                if( !empty($driver) )
                {
                    swoole_timer_clear($timer_id);
                    $driver->async_query($sql, $promise);
                }
            });
        }
        $driver->async_query($sql, $promise);
    }
}