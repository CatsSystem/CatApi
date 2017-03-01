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
use base\framework\cache\CacheLoader;
use common\Constants;
use base\log\Log;

class HttpServer extends Http
{
    public function onWorkerStart($server, $workerId)
    {
        parent::onWorkerStart($server, $workerId);
        Config::load();
        if(!$server->taskworker) {
            Pool::getInstance()->init();
            AsyncRedis::getInstance()->connect(new Promise());
            CacheLoader::getInstance()->init();
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
                if( is_array($result) ) {
                    $result = json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }
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

    public function before_start()
    {
        $process = new \swoole_process(function(\swoole_process $worker) {
            $worker->name(Config::get('project_name') . " cache process");
            CacheLoader::getInstance()->init();
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
