<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/2/9
 * Time: 21:29
 */

namespace base\timer;

use base\common\Factory;

class Timer
{

    public static function after($ms, $name, $params)
    {
        $action = '\\timer\\' . $name;
        $class = Factory::getInstance($action);

        if (!($class instanceof ITimer)) {
            throw new \Exception("timer not found");
        } else {
            $class->setParams($params);
            swoole_timer_after($ms, [$class, 'doAction']);
        }
    }

    private static $instance = [];

    public static function tick($ms, $name, $params)
    {
        if( isset(Timer::$instance["$ms:$name"]) ) {
            return;
        }
        $action = '\\timer\\' . $name;
        $class = Factory::getInstance($action);

        if (!($class instanceof ITimer)) {
            throw new \Exception("timer not found");
        } else {
            $class->setParams($params);
            $timer_id = swoole_timer_tick($ms, [$class, 'doAction']);
            Timer::$instance["$ms:$name"] = $timer_id;
        }
    }

    public static function tick_cancel($ms, $name)
    {
        $timer_id = Timer::$instance["$ms:$name"];
        swoole_timer_clear($timer_id);
    }


}