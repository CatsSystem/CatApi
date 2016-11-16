<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/10/26
 * Time: 下午5:49
 */

namespace base\model;


use GuzzleHttp\Promise\Promise;

abstract class Driver
{
    private static $instance = [];

    /**
     * @param $name
     * @param int $id
     * @return mixed
     */
    public static function newInstance($name, $id = 0)
    {
        $class_name = __NAMESPACE__ . "\\driver\\{$name}";
        return new $class_name($id);
    }

    abstract public function connect($reconnect = false, Promise $promise = null);

    abstract public function close();

    abstract public function query($sql, $is_query = false);

    abstract public function async_query($sql, Promise $promise, $is_query = false);

}