<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/4/12
 * Time: 上午10:06
 */

namespace base\log;

abstract class Logger
{
    public function __construct($config)
    {
        $this->level = $config['log_level'];
    }

    public function debug($tag, $content)
    {
        if( $this->level >= 2 )
        {
            return;
        }
        $path = $tag . "_DEBUG_" . date("Y-m-d");
        $this->save($path, $content);
    }
    public function error($tag, $content)
    {
        if( $this->level >= 5)
        {
            return;
        }
        $path = $tag . "_ERROR_" . date("Y-m-d");
        $this->save($path, $content);
    }
    public function info($tag, $content)
    {
        if( $this->level >= 4)
        {
            return;
        }
        $path = $tag . "_INFO_" . date("Y-m-d");
        $this->save($path, $content);
    }
    public function warning($tag, $content)
    {
        if( $this->level >= 3)
        {
            return;
        }
        $path = $tag . "_WARNING_" . date("Y-m-d");
        $this->save($path, $content);
    }

    abstract protected function save($path, $content);
}