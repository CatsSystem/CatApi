<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/4/12
 * Time: 上午10:07
 */

namespace base\log;

use base\config\Config;

class Log
{

    private static $instance = null;
    private static function getInstance()
    {
        if(self::$instance == null )
        {
            self::$instance = new Log(Config::get('log'));
        }
        return self::$instance;
    }

    private $logger;

    private function __construct($config)
    {

        $class_name = __NAMESPACE__. "\\adapter\\" . $config['adapter'];
        if( !class_exists($class_name) )
        {
            $class_name = __NAMESPACE__. '\\adapter\\File';
        }

        $this->logger = new $class_name($config);
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public static function DEBUG($TAG, $content)
    {
        self::getInstance()->getLogger()->debug($TAG, $content);
    }

    public static function INFO($TAG, $content)
    {
        self::getInstance()->getLogger()->info($TAG, $content);
    }

    public static function ERROR($TAG, $content)
    {
        self::getInstance()->getLogger()->error($TAG, $content);
    }

    public static function WARNING($TAG, $content)
    {
        self::getInstance()->getLogger()->warning($TAG, $content);
    }
    
    public static function myJson($data)
    {
        return json_encode($data,  JSON_UNESCAPED_UNICODE);
    }

}