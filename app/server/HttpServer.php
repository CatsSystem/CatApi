<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/6/15
 * Time: 上午11:03
 */

namespace server;

use api\Api;
use base\async\AsyncRedis;
use base\model\Pool;
use base\core\Config;
use base\Enterance;
use base\server\adapter\BaseCallback;
use GuzzleHttp\Promise\Promise;
use lib\Json;

class HttpServer extends BaseCallback
{
    /**
     * @var \swoole_server
     */
    public static $server;

    public function onWorkerStart($server, $workerId)
    {
        parent::onWorkerStart($server, $workerId);
        Config::load(Enterance::$rootPath . '/config');

        if($server->taskworker) {

        } else {
            Pool::getInstance()->init();
            AsyncRedis::getInstance()->connect(new Promise());
        }
    }

    public function onRequest(\swoole_http_request $request, \swoole_http_response $response)
    {
        $path = $request->server['path_info'];
        $path = str_replace('/', "\\", $path);
        $method_name = "api\\Api\\{$path}";

        if( function_exists($method_name) )
        {
            call_user_func($method_name, $request, $response);
        }

    }

    public function onTask(\swoole_server $server, $task_id, $from_id, $data)
    {

    }

    public function onFinish()
    {

    }

    public function setServer($server)
    {
        HttpServer::$server = $server;
    }

    public function onPipeMessage(\swoole_server $server, $from_worker_id, $message)
    {
        $data = json_decode($message, true);
    }

    public function beforeStart(\swoole_server $server)
    {
        // 添加工作进程,刷新缓存
        $process = new \swoole_process(function(\swoole_process $worker) use ($server){
            $worker->name(Config::get('project_name') . " cache process");
            swoole_timer_tick(10000, function(){

            });
        }, false, false);
        $server->addProcess($process);
    }
}