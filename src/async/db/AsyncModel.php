<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/10/26
 * Time: 下午6:25
 */

namespace base\async\db;

use base\promise\Promise;

class AsyncModel
{
    private $table;

    public function __construct($table)
    {
        $this->table = $table;
    }

    public function query($sql, Promise $promise, $timeout = 3000)
    {
        $driver = Pool::getInstance()->get($sql, $promise, $timeout);
        if(empty($driver))
        {
            return;
        }
        $driver->async_query($sql, $promise, $timeout);
    }
}