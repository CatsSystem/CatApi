<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/4/12
 * Time: 上午10:08
 */

namespace base\log\adapter;

use base\log\Logger;

class File extends Logger
{
    const SEPARATOR = " | ";

    private $file_path;
    private $config;
    private $file = [];

    public function __construct($config)
    {
        $this->config = $config;
        $this->file_path = isset($config['path']) ? $config['path'] : '/var/log/swoole/';

        if( !file_exists($this->file_path) )
        {
            @mkdir($this->file_path, 0755, true);
        }

    }

    protected function save($path, $content)
    {
        if( !$this->config['open_log'] )
        {
            return;
        }
        $log_file = $this->file_path . $path;
        if( !isset($this->file[$path]) )
        {
            $this->file[$path] = fopen($log_file,'a');
        }
        $str = date("Y-m-d H:i:s") . self::SEPARATOR . $content;
        fwrite( $this->file[$path], $str . "\r\n");
    }
}