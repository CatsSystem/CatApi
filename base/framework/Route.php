<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/11/29
 * Time: 下午11:35
 */
namespace base\framework;

use base\config\Config;
use base\protocol\Request;

class Route
{
    public static function route(Request $request, callable $callback)
    {
        try {
            $action = Config::get('ctrl_path', 'api') . '\\' . $request->getModule() . '\\' . $request->getCtrl();
            if (!\class_exists($action)) {
                throw new \Exception("no class {$action}");
            }
            $class =  new $action();
            $method = $request->getMethod();
            if ( !($class instanceof BaseController) || !method_exists($class, $method)) {
                throw new \Exception("method error");
            }
            $request->setCallback($callback);
            if( $class->before($request) ) {
                $class->$method();
            }
        }catch (\Exception $e) {
            $result =  \call_user_func('base\Enterance::exceptionHandler', $e);
            if( !Config::get('debug', false) )
            {
                $result = "Error in Server";
            }
            call_user_func($callback, $result);
        }
    }
}