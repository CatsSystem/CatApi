<?php
/**
 * author: shenzhe
 * Date: 13-6-17
 */
namespace base\common;

class Factory
{
    public static function getInstance($className)
    {
        if (!\class_exists($className)) {
            throw new \Exception("no class {$className}");
        }
        return new $className();
    }
}
