<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/6/15
 * Time: 上午11:03
 */

namespace server;

use base\async\cache\AsyncRedis;
use base\async\db\Pool;
use base\config\Config;
use base\framework\Route;
use base\promise\Promise;
use base\protocol\Request;
use base\socket\adapter\Http;
use base\task\Task;
use base\task\TaskRoute;
use cache\CacheLoader;
use common\Constants;
use log\Log;

class HttpServer extends Http
{
    /**
     * @var \swoole_server
     */
    public static $static_server;

    public function onWorkerStart($server, $workerId)
    {
        self::$static_server = $server;
        parent::onWorkerStart($server, $workerId);
        Config::load();

        Pool::getInstance()->init();
        AsyncRedis::getInstance()->connect(new Promise());

        if(!$server->taskworker) {
            CacheLoader::getInstance()->init($server);
        }
    }

    /**
     * @param \swoole_http_request $request
     * @param \swoole_http_response $response
     * @throws \Exception
     */
    public function onRequest(\swoole_http_request $request, \swoole_http_response $response)
    {
        if( !in_array($request->server['path_info'], Config::get('route'))) {
            $response->status(403);
            $response->end("");
            return;
        }

        $path = explode('/' , $request->server['path_info']);
        $module     = isset( $path[1] ) ? $path[1] : "";
        $controller = isset( $path[2] ) ? $path[2] : "";
        $method     = isset( $path[3] ) ? $path[3] : "";

        $handle = new Request();
        $handle->setRequest($request);
        $handle->setResponse($response);
        $handle->setSocket($this->server);
        
        $handle->init($module, $controller, $method,
            isset( $request->post ) ? $request->post : $request->rawContent());

        try {
            Route::route($handle, function($result) use ($handle, $request, $response) {
                $response->end($result);
            });
        } catch ( \Exception $e ) {
            Log::ERROR('Exception', var_export($e, true));
        } catch ( \Error $e ) {
            Log::ERROR('Exception', var_export($e, true));
        }

        $response->status(502);
        $response->end("");
    }

    public function onTask(\swoole_server $server, $task_id, $from_id, $data)
    {
        $task = new Task($data);
        $result = TaskRoute::route($task);
        return $result;
    }

    public function onFinish(\swoole_server $serv, $task_id, $data)
    {

    }

    public function before_start()
    {
        $process = new \swoole_process(function(\swoole_process $worker) {
            $worker->name(Config::get('project_name') . " cache process");
            CacheLoader::getInstance()->init($this->server);
            AsyncRedis::getInstance()->connect(new Promise());
            Pool::getInstance()->init(function(){
                CacheLoader::getInstance()->load(true);
                swoole_timer_tick(Constants::ONE_TICK, function(){
                    CacheLoader::getInstance()->load();
                });
            });
        }, false, false);
        $this->server->addProcess($process);
    }
}
