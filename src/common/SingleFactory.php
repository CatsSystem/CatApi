<?php
/**
 * author: shenzhe
 * Date: 13-6-17
 */
namespace base\common;

class SingleFactory
{
    private static $instances = array();

    public static function getInstance($className)
    {
        $keyName = $className;
        if (isset(self::$instances[$keyName])) {
            return self::$instances[$keyName];
        }
        if (!\class_exists($className)) {
            throw new \Exception("no class {$className}");
        }

        self::$instances[$keyName] = new $className();

        return self::$instances[$keyName];
    }
}
