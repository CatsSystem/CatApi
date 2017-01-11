<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/1/5
 * Time: 下午3:26
 */

namespace base\framework;

use base\protocol\Request;

class BaseController
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var array
     */
    protected $params;

    /**
     * @var array
     */
    protected $global_data;

    public function before(Request &$request)
    {
        $this->request = $request;
        $this->params = $request->getParams();
        return true;
    }

    /**
     * @param $task     string
     * @param $method   string
     * @param $param    mixed
     * @param $callback callable
     */
    protected function sendTask($task, $method, $param, $callback)
    {
        $data = json_encode([
            'task'  => $task,
            'method'=> $method,
            'params'  => $param
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $this->request->getSocket()->task($data, -1, function(\swoole_server $serv, $task_id, $data) use ($callback) {
            if( is_callable($callback) ) {
                $callback(json_decode($data, true));
            }
        });
    }
}