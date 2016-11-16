<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/4/8
 * Time: 下午5:40
 */

namespace base\core;

use base\protocol\Request;
use base\server\IController;
use service\ServiceAPI;

class Route
{
    public static function route(Request $request)
    {
        $action = Config::get('ctrl_path', 'ctrl') . '\\' . $request->getCtrl();
        try {
            $class = Factory::getInstance($action);
            if (!($class instanceof IController)) {

                throw new \Exception("ctrl error");
            } else {
                $result = null;
                if($class->_before()) {
                    $method = $request->getMethod();
                    if (!method_exists($class, $method)) {
                        throw new \Exception("method error");
                    }
                    $result = $class->$method();
                }
                $class->_after();
                if (null === $result) {
                    return null;
                }
                return json_encode($result, JSON_UNESCAPED_UNICODE);
            }
        }catch (\Exception $e) {
            $result =  \call_user_func(Config::getField('project', 'exception_handler', 'base\Enterance::exceptionHandler'), $e);
            if( isset($class) && $class instanceof IController) {
                $class->_after();
            }
            ServiceAPI::getInstance()->trace(Config::getField('service', 'service_id'), Formater::exception($e));
            return $result;
            
        }
    }
}
