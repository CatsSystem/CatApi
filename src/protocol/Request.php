<?php
/**
 * User: shenzhe
 * Date: 13-6-17
 */


namespace base\protocol;

use base\config\Config;

class Request
{
    private $_params;
    private $_query_string;

    private $_module = 'home';

    private $_ctrl = 'Index';

    private $_method = 'index';

    /**
     * @var \swoole_http_request
     */
    private $_request = null;

    /**
     * @var \swoole_http_response
     */
    private $_response = null;

    /**
     * @var \swoole_http_server
     */
    private $_socket = null;

    private $callback;

    private $_server;

    /**
     * @var Request[]
     */
    private static $instances = [];

    public function __construct()
    {
        self::$instances[] = $this;
    }

    public function __destruct()
    {
        $index = array_search($this, self::$instances, true);
        if( $index !== false )
        {
            unset(self::$instances[$index]);
        }
    }

    public static function exception()
    {
        foreach (self::$instances as $request)
        {
            $request->getResponse()->status(503);
            $request->getResponse()->end("");
            unset($request);
        }
        unset(self::$instances);
    }

    public function init($module, $ctrl, $method, $params)
    {
        if($module) {
            $this->_module = $module;
        } else {
            $this->_module = Config::getField('project', 'default_module', $this->_module);
        }

        if($ctrl) {
            $this->_ctrl = $ctrl;
        } else {
            $this->_ctrl = Config::getField('project', 'default_ctrl', $this->_ctrl);
        }
        if($method) {
            $this->_method = $method;
        }else {
            $this->_method = Config::getField('project', 'default_method', $this->_method);
        }
        if( !is_array($params) )
        {
            $this->_query_string = $params;
            $params = json_decode($params, true);
        }
        $this->_params = $params;

        if(!is_string($this->_ctrl) || !is_string($this->_method)) {
            throw new \Exception('ctrl or method no string');
        }
    }

	public function setParams($params)
    {
        $this->_params = $params;
    }

    public function addParams($key, $val, $set=true)
    {
        if($set || !isset($this->_params[$key])) {
            $this->_params[$key] = $val;
        }
    }

    public function getParams()
    {
        return $this->_params;
    }

    public function setCtrl($ctrlName)
    {
        $this->_ctrl = $ctrlName;
    }

    public function getCtrl()
    {
        return $this->_ctrl;
    }

    public function setMethod($methodName)
    {
        $this->_method = $methodName;
    }

    public function getMethod()
    {
        return $this->_method;
    }

    public function setServer($server)
    {
        $this->_server = $server;
    }

    public function getServer()
    {
        return $this->_server;
    }

    public function setRequest($request)
    {
        $this->_request = $request;
    }

    public function getRequest()
    {
        return $this->_request;
    }

    public function setSocket($socket)
    {
        $this->_socket = $socket;
    }

    public function getSocket()
    {
        return $this->_socket;
    }

    public function getPathInfo()
    {
        return isset($this->_request->header['path_info']) ? $this->_request->header['path_info'] : '';
    }

    /**
     * @return \swoole_http_response
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * @param \swoole_http_response $response
     */
    public function setResponse($response)
    {
        $this->_response = $response;
    }

    /**
     * @param $result
     * @param bool $exit
     * @throws \Exception
     */
    public function callback($result, $exit = false)
    {
        if( is_callable($this->callback) )
        {
            call_user_func($this->callback, $result);
        }
        if( $exit ) {
            throw new \Exception('exit');
        }
    }

    /**
     * @param mixed $callback
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;
    }

    /**
     * @return mixed
     */
    public function getQueryString()
    {
        return $this->_query_string;
    }

    /**
     * @return string
     */
    public function getModule()
    {
        return $this->_module;
    }

    /**
     * @param string $module
     */
    public function setModule($module)
    {
        $this->_module = $module;
    }

}
