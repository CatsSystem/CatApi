<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/4/8
 * Time: 下午4:29
 */

namespace base\server;

use api\cache\FileCache;
use base\core\Config;

class SwooleServer
{
    private $_server;
    private $_callback;

    private $config;

    public function __construct(array $config)
    {
        if(!\extension_loaded('swoole')) {
            throw new \Exception("no swoole extension. get: https://github.com/swoole/swoole-src");
        }

        $this->config = $config;
        $this->_server = new \swoole_http_server($config['host'], $config['port']);
        $this->_server->set($config);
    }

    public function setCallback($callback)
    {
        if( !( $callback instanceof ICallback ) )
        {
            throw new \Exception('client must object');
        }
        $this->_callback = $callback;
        $this->_callback->setServer($this->_server);
    }

    public function run()
    {
        $handlerArray = array(
            'onTimer',
            'onWorkerStart',
            'onWorkerStop',
            'onWorkerError',
            'onTask',
            'onFinish',
            'onManagerStart',
            'onManagerStop',
        );
        $this->_server->on('Start', array($this->_callback, 'onStart'));
        $this->_server->on('Shutdown', array($this->_callback, 'onShutdown'));
        $this->_server->on('Connect', array($this->_callback, 'onConnect'));
        $this->_server->on('Close', array($this->_callback, 'onClose'));
        $this->_server->on('Request', array($this->_callback, 'onRequest'));

        foreach($handlerArray as $handler) {
            if(method_exists($this->_callback, $handler)) {
                $this->_server->on(\substr($handler, 2), array($this->_callback, $handler));
            }
        }
        if( !FileCache::getInstance()->loadRankCache(Config::getField('file_cache', 'path')) ){
            exit("load cache error");
        }
        $this->_server->start();
    }
        
}
