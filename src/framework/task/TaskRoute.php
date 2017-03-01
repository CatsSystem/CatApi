<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/12/6
 * Time: 下午11:09
 */
namespace base\framework\task;

use base\common\Factory;
use base\config\Config;

class TaskRoute
{
    public static function route(Task $task)
    {
        try {
            $action = '\\task\\' . $task->getTask();
            $class = Factory::getInstance($action);

            if (!($class instanceof IRunner)) {
                throw new \Exception("task error");
            } else {
                $method = $task->getMethod();
                if (!method_exists($class, $method)) {
                    throw new \Exception("method error");
                }
                $result = $class->$method($task->getParams());
                return json_encode($result, JSON_UNESCAPED_UNICODE);
            }
        }catch (\Exception $e) {
            $result =  \call_user_func('base\Enterance::exceptionHandler', $e);
            if( !Config::get('debug', false) )
            {
                $result = "Error in Server";
            }
            return $result;
        }
    }
}