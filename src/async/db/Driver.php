<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/10/26
 * Time: 下午5:49
 */

namespace base\async\db;


use base\promise\Promise;

abstract class Driver
{
    protected $id;
    protected $is_close = false;

    protected $config;

    /**
     * @param $name
     * @param int $id
     * @return mixed
     */
    public static function newInstance($name, $id = 0)
    {
        $class_name = __NAMESPACE__ . "\\adapter\\{$name}";
        return new $class_name($id);
    }

    abstract public function connect($reconnect = false, Promise $promise = null);

    abstract public function close();

    abstract public function async_query($sql, Promise $promise, $timeout);

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function isClose()
    {
        return $this->is_close;
    }
}