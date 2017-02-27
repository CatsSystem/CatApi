<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/4/8
 * Time: 下午4:29
 */

namespace base\socket;

class SwooleServer
{
    private static $instance = null;

    /**
     * @return SwooleServer
     */
    public static function getInstance()
    {
        if(SwooleServer::$instance == null)
        {
            SwooleServer::$instance = new SwooleServer();
        }
        return SwooleServer::$instance;
    }
    
    protected function __construct()
    {

    }

    /**
     * @var \swoole_server
     */
    private $_server;
    /**
     * @var BaseCallback
     */
    private $_callback;

    private $config;

    public function init(array $config)
    {
        if(!\extension_loaded('swoole')) {
            throw new \Exception("no swoole extension. get: https://github.com/swoole/swoole-src");
        }

        $this->config = $config;

        switch ($config['socket_type'])
        {
            case 'tcp':
            {
                $this->_server = new \swoole_server($config['host'], $config['port'], $config['mode'], SWOOLE_TCP);
                break;
            }
            case 'udp':
            {
                $this->_server = new \swoole_server($config['host'], $config['port'], $config['mode'], SWOOLE_UDP);
                break;
            }
            case 'http':
            {
                $this->_server = new \swoole_http_server($config['host'], $config['port'], $config['mode']);
                break;
            }
            case 'https':
            {
                $this->_server = new \swoole_http_server($config['host'], $config['port'], $config['mode'], SWOOLE_TCP | SWOOLE_SSL);
                break;
            }
            case 'ws':
            {
                $this->_server = new \swoole_websocket_server($config['host'], $config['port'], $config['mode']);
                break;
            }
            case 'wss':
            {
                $this->_server = new \swoole_websocket_server($config['host'], $config['port'], $config['mode'], SWOOLE_TCP | SWOOLE_SSL);
                break;
            }
        }

        $this->_server->set($config);
        return $this;
    }

    public function setCallback($callback)
    {
        if( !( $callback instanceof BaseCallback ) )
        {
            throw new \Exception('client must object');
        }
        $this->_callback = $callback;
        $this->_callback->setServer($this->_server);
    }

    public function run()
    {
        $handlerArray = array(
            'onWorkerStart',
            'onWorkerStop',
            'onWorkerError',

            'onTask',
            'onFinish',

            'onManagerStart',
            'onManagerStop',

            'onPipeMessage',

            'onHandShake',
            'onOpen',
        );
        $this->_server->on('Start', array($this->_callback, 'onStart'));
        $this->_server->on('Shutdown', array($this->_callback, 'onShutdown'));
        $this->_server->on('Connect', array($this->_callback, 'onConnect'));
        $this->_server->on('Close', array($this->_callback, 'onClose'));

        switch ($this->config['socket_type'])
        {
            case 'tcp':
            {
                $this->_server->on('Receive', array($this->_callback, 'onReceive'));
                break;
            }
            case 'udp':
            {
                $this->_server->on('Packet', array($this->_callback, 'onPacket'));
                break;
            }
            case 'http':
            case 'https':
            {
                $this->_server->on('Request', array($this->_callback, 'onRequest'));
                break;
            }
            case 'ws':
            case 'wss':
            {
                $this->_server->on('Request', array($this->_callback, 'onRequest'));
                $this->_server->on('Message', array($this->_callback, 'onMessage'));
                break;
            }
        }


        foreach($handlerArray as $handler) {
            if(method_exists($this->_callback, $handler)) {
                $this->_server->on(\substr($handler, 2), array($this->_callback, $handler));
            }
        }
        
        $this->_callback->before_start();
        $this->_server->start();
    }

    /**
     * @return \swoole_server
     */
    public function getServer()
    {
        return $this->_server;
    }

}
