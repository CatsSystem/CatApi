<?php

namespace base;

use base\common\Formater;
use base\socket\SwooleServer;
use base\config\Config;

class Enterance
{
    private static $classPath = array();
    public static $rootPath;
    public static $configPath;

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
        var_dump($exception);
        if( $exception instanceof \Error) {
            return $exception;
        }
        return var_export(Formater::exception($exception), true);
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

    public static function run($runPath, $configPath)
    {
        self::$rootPath = $runPath;
        self::$configPath = $runPath . '/config/' . $configPath;

        \spl_autoload_register(__CLASS__ . '::autoLoader');

        Config::load(self::$configPath);

        //\set_exception_handler( __CLASS__ . '::exceptionHandler' );
        \register_shutdown_function( __CLASS__ . '::fatalHandler' );

        $timeZone = Config::get('time_zone', 'Asia/Shanghai');
        \date_default_timezone_set($timeZone);

        $service = SwooleServer::getInstance()->init(Config::get('socket'));
        
        $callback = Config::get('callback');
        $service->setCallback( new $callback() );
        $service->run();
    }
}

