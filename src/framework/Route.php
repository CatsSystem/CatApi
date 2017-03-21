<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/11/29
 * Time: 下午11:35
 */
namespace base\framework;

use base\protocol\Request;
use core\common\Error;
use core\component\config\Config;

class Route
{
    /**
     * @param Request $request
     * @return mixed|string
     */
    public static function route(Request $request)
    {
        try {
            $action = Config::getField('project','ctrl_path', 'api') . '\\' . $request->getModule() . '\\' . $request->getCtrl();
            if (!\class_exists($action)) {
                throw new \Exception("no class {$action}");
            }
            $class =  new $action();
            $method = $request->getMethod();
            if ( !($class instanceof BaseController) || !method_exists($class, $method)) {
                throw new \Exception("method error");
            }

            if( $class->before($request) ) {
                return yield $class->$method();
            }
            return "";
        } catch (\Exception $e) {
            return self::handleException($e);
        } catch (\Error $e) {
            return self::handleException($e);
        }
    }

    /**
     * @param $e \Exception | \Error
     * @return array|mixed|string
     */
    private static function handleException($e)
    {
        $result =  \call_user_func('base\Entrance::exceptionHandler', $e);
        if( !Config::get('debug', true) )
        {
            $result = "Error in Server";
        }
        return $result;
    }
}