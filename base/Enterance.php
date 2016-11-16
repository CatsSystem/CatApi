<?php

namespace base;

use base\core\Formater;
use base\server\SwooleServer;
use base\core\Config;

class Enterance
{
    private static $classPath = array();
    public static $rootPath;

    final public static function autoLoader($class)
    {
        if(isset(self::$classPath[$class])) {
            require self::$classPath[$class];
            return;
        }
        $baseClasspath = \str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
        $libs = array(
            self::$rootPath . DIRECTORY_SEPARATOR . 'app',
            self::$rootPath,
        );
        foreach ($libs as $lib) {
            $classpath = $lib . DIRECTORY_SEPARATOR . $baseClasspath;
            if (\is_file($classpath)) {
                self::$classPath[$class] = $classpath;
                require "{$classpath}";
                return;
            }
        }
    }

    final public static function exceptionHandler($exception)
    {
        return json_encode(Formater::exception($exception));
    }

    final public static function fatalHandler()
    {
        $error = \error_get_last();
        if(empty($error)) {
            return '';
        }
        if(!in_array($error['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR))) {
            return '';
        }

        return json_encode(Formater::fatal($error));
    }

    final public static function customError($errno, $errstr, $errfile, $errline)
    {
        $result['errno'] = $errno;
        $result['errstr'] = $errstr;
        $result['errfile'] = $errfile;
        $result['errline'] = $errline;
        //ServiceAPI::getInstance()->trace(Config::getField('service', 'service_id'), $result );
        return false;
    }

    public static function run($runPath)
    {
        self::$rootPath = $runPath;

        \spl_autoload_register(__CLASS__ . '::autoLoader');

        Config::load($runPath . '/config');

        \set_exception_handler( __CLASS__ . '::exceptionHandler' );
        \register_shutdown_function( __CLASS__ . '::fatalHandler' );
        set_error_handler(__CLASS__ . '::customError');

        $timeZone = Config::get('time_zone', 'Asia/Shanghai');
        \date_default_timezone_set($timeZone);

        $service = new SwooleServer(Config::get('socket'));
        
        $callback = Config::get('callback');
        $service->setCallback( new $callback() );
        $service->run();
    }
}

