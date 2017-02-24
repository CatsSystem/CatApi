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
use base\socket\adapter\Ws;
use base\task\Task;
use base\task\TaskRoute;
use cache\CacheLoader;
use common\Constants;
use base\log\Log;

class WebSocket extends Ws
{
    /**
     * @var \swoole_websocket_server
     */
    public static $static_server;

    public function onWorkerStart($server, $workerId)
    {
        self::$static_server = $server;
        parent::onWorkerStart($server, $workerId);
        Config::load();

        if(!$server->taskworker) {
            Pool::getInstance()->init();
            AsyncRedis::getInstance()->connect(new Promise());
            CacheLoader::getInstance()->init($server);
        }
    }

    public function onOpen(\swoole_websocket_server $server, \swoole_http_request $req)
    {
        parent::onOpen($server, $req);
    }

    public function onMessage(\swoole_websocket_server $server, \swoole_websocket_frame $frame)
    {
        $data = json_decode($frame->data, true);
        if( !in_array("/{$data['m']}/{$data['c']}/{$data['a']}", Config::get('route'))) {
            return;
        }
        $handle = new Request();
        $handle->setSocket($this->server);
        $data['fd'] = $frame->fd;
        $handle->init($data['m'], $data['c'], $data['a'], $data);

        try {
            Route::route($handle, function($result) use ($handle, $server, $frame) {
                if( is_array($result) ) {
                    $result = json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }
                $server->push($frame->fd, $result);
            });
        } catch ( \Exception $e ) {
            Log::ERROR('Exception', var_export($e, true));
            $server->push($frame->fd, var_export($e, true));
        } catch ( \Error $e ) {
            Log::ERROR('Exception', var_export($e, true));
            $server->push($frame->fd, var_export($e, true));
        }
    }

    public function onClose(\swoole_server $server, $fd, $from_id)
    {
        parent::onClose($server, $fd, $from_id);
        if(self::check_ws($fd) )
        {
            // 是WebSocket连接, 处理离线逻辑
            $handle = new Request();
            $handle->setSocket($this->server);
            $data['fd'] = $fd;
            $handle->init("home", "Index", "Logout", []);
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
                var_dump($result);
                if( is_array($result) ) {
                    $result = json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }
                $response->header('Content-type', 'application/json');
                $response->end($result);
            });
        } catch ( \Exception $e ) {
            Log::ERROR('Exception', var_export($e, true));
            $response->status(502);
            $response->end("");
        } catch ( \Error $e ) {
            Log::ERROR('Exception', var_export($e, true));
            $response->status(502);
            $response->end("");
        }
    }

    public function onPipeMessage(\swoole_server $server, $from_worker_id, $message)
    {
        $data = json_decode($message, true);
        CacheLoader::getInstance()->set($data['id'], $data['data']);
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

    public static function check_ws($fd)
    {
        $connection_info = self::$static_server->connection_info($fd);
        if( isset($connection_info['websocket_status']) )
        {
            return true;
        }
        return false;
    }
}
