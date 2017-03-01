<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/2/9
 * Time: 21:29
 */

namespace base\framework\timer;

class Timer
{

    public static function after($ms, $name, $params)
    {
        $action = '\\timer\\' . $name;
        if (!\class_exists($action)) {
            throw new \Exception("no class {$action}");
        }
        $class = new $action("");

        if (!($class instanceof ITimer)) {
            throw new \Exception("timer not found");
        } else {
            $class->setParams($params);
            swoole_timer_after($ms, [$class, 'doAction']);
        }
    }

    private static $instance = [];

    public static function tick($ms, $name, $key, $params)
    {
        if( isset(Timer::$instance["$ms:$name:$key"]) ) {
            return;
        }
        $action = '\\timer\\' . $name;
        if (!\class_exists("$ms:$name:$key")) {
            throw new \Exception("no class {$action}");
        }
        $class = new $action();

        if (!($class instanceof ITimer)) {
            throw new \Exception("timer not found");
        } else {
            $class->setParams($params);
            $timer_id = swoole_timer_tick($ms, [$class, 'doAction']);
            Timer::$instance["$ms:$name:$key"] = $timer_id;
        }
    }

    public static function tick_cancel($ms, $name, $key)
    {
        $timer_id = Timer::$instance["$ms:$name:$key"];
        swoole_timer_clear($timer_id);
    }


}