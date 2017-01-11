<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/4/8
 * Time: 下午5:40
 */

namespace base\config;

use base\Enterance;

class Config
{

    private static $config;
    private static $configPath;

    public static function load($configPath = "")
    {
        if( empty($configPath) ) {
            $configPath = Enterance::$configPath;
        }
        $files = Config::tree($configPath, "/.php$/");
        $config = array();

        if (!empty($files)) {
            foreach ($files as $file) {
                $config += include "{$file}";
            }
        }

        self::$config = $config;
        self::$configPath = $configPath;

        return $config;
    }

    public static function loadFiles(array $files)
    {
        $config = array();
        foreach ($files as $file) {
            $config += include "{$file}";
        }
        self::$config = $config;
        return $config;
    }

    public static function get($key, $default = null, $throw = false)
    {
        $result = isset(self::$config[$key]) ? self::$config[$key] : $default;
        if ($throw && is_null($result)) {
            throw new \Exception("{key} config empty");
        }
        return $result;
    }

    public static function set($key, $value, $set = true)
    {
        if ($set) {
            self::$config[$key] = $value;
        } else {
            if (empty(self::$config[$key])) {
                self::$config[$key] = $value;
            }
        }
        return true;
    }

    public static function getField($key, $filed, $default = null, $throw = false)
    {
        $result = isset(self::$config[$key][$filed]) ? self::$config[$key][$filed] : $default;
        if ($throw && is_null($result)) {
            throw new \Exception("{key} config empty");
        }
        return $result;
    }

    public static function all()
    {
        return self::$config;
    }

    public static function tree($dir, $filter = '', &$result = array(), $deep = false)
    {
        $files = new \DirectoryIterator($dir);
        foreach ($files as $file) {
            $filename = $file->getFilename();
            if ($filename[0] === '.') {
                continue;
            }

            if ($file->isDir()) {
                self::tree($dir . DIRECTORY_SEPARATOR . $filename, $filter, $result, $deep);
            } else {
                if(!empty($filter) && !\preg_match($filter,$filename)){
                    continue;
                }
                if ($deep) {
                    $result[$dir] = $filename;
                } else {
                    $result[] = $dir . DIRECTORY_SEPARATOR . $filename;
                }
            }
        }
        return $result;
    }
}
