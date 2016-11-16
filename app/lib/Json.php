<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/6/21
 * Time: 上午10:57
 */

namespace lib;

class Json
{
    public static function json_encode($params, $options = 0)
    {
        if(extension_loaded('jsond'))
        {
            return jsond_encode($params, $options);
        } else {
            return json_encode($params, $options);
        }
    }

    public static function json_decode($json , $assoc = false)
    {
        if(extension_loaded('jsond'))
        {
            return jsond_decode($json, $assoc);
        } else {
            return json_decode($json, $assoc);
        }
    }
}