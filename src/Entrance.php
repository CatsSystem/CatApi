<?php

namespace base;

use base\socket\SwooleServer;
use core\common\Formater;
use core\component\config\Config;

class Entrance
{
    public static $rootPath;
    public static $configPath;

    /**
     * @param $exception  \Exception | \Error
     * @return mixed
     * @throws \Exception
     */
    final public static function exceptionHandler($exception)
    {
        if(Config::get('debug', true)) {
            var_dump([
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine()
            ]);
        }
        if( $exception instanceof \Error) {
            return $exception;
        }
        return json_encode(Formater::exception($exception),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    final public static function fatalHandler()
    {
        $error = \error_get_last();
        return json_encode(Formater::fatal($error), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public static function run($runPath, $configPath)
    {
        self::$rootPath = $runPath;
        self::$configPath = $runPath . '/config/' . $configPath;

        Config::load(self::$configPath);
        \register_shutdown_function( __CLASS__ . '::fatalHandler' );

        $timeZone = Config::get('time_zone', 'Asia/Shanghai');
        \date_default_timezone_set($timeZone);

        $service = SwooleServer::getInstance()->init(Config::get('server'));

        $callback = Config::getField('project', 'main_callback');
        if( !class_exists($callback) )
        {
            throw new \Exception("No class {$callback}");
        }
        $callback = new $callback();
        $service->setCallback($callback);
        $service->run();
    }
}

