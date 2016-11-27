<?php
/**
 * Created by PhpStorm.
 * User: lancelot
 * Date: 16-11-27
 * Time: 上午12:39
 */

namespace api;

use base\async\AsyncHttpClient;
use base\async\AsyncRedis;
use base\model\AsyncModel;
use base\promise\PromiseGroup;
use GuzzleHttp\Promise\Promise;
use server\HttpServer;

class Test
{
    public function TaskTest(\swoole_http_request $request, \swoole_http_response $response)
    {
        $promise_group = new PromiseGroup();
        $promise_group->add("task", function(Promise $promise) use ($request) {
            // 发起任务
            $data['op'] = 1;
            $data['data'] = $request->rawContent();
            HttpServer::$server->task( json_encode($data) , -1 , function(\swoole_server $server, $task_id, $data) use ($promise) {
                $promise->resolve($data);
            });
        });
        $promise_group->add("task_2", function(Promise $promise){
            // 发起任务
            HttpServer::$server->task("task 2 data", -1 , function(\swoole_server $server, $task_id, $data) use ($promise) {
                $promise->resolve($data);
            });
        });
        $promise = new Promise();
        $promise->then(function($value) use ($request, $response) {
            $data = $value['task'];
            $data_2 = $value['task_2'];

            // do

            $response->end($data);
        });
        $promise_group->setPromise($promise);
        $promise_group->run();
    }

    public function ModelTest(\swoole_http_request $request, \swoole_http_response $response)
    {
        $promise_group = new PromiseGroup();
        $promise_group->add("model_1", function(Promise $promise){
            // 创建异步Model
            $model = new AsyncModel("Test_1");
            // 发起请求
            $model->query("select * from Test_1", $promise);
        });
        $promise_group->add("model_2", function(Promise $promise){
            // 创建异步Model
            $model = new AsyncModel("Test_2");
            // 发起请求
            $model->query("select * from Test_2", $promise);
        });
        $promise = new Promise();
        $promise->then(function($value){

            $data_1 = $value['model_1'];
            $data_2 = $value['model_2'];

            $promise = new Promise();
            // 创建异步Model
            $model = new AsyncModel("test");
            // 发起请求
            $model->query("select * from Test_3", $promise);

            return $promise;
        })->then(function($value) use ($request, $response) {

            $response->end($value);
        });
        $promise_group->setPromise($promise);
        $promise_group->run();
    }

    public function RedisTest(\swoole_http_request $request, \swoole_http_response $response)
    {
        $promise_group = new PromiseGroup();
        $promise_group->add("redis_1", function(Promise $promise){
            // 发起异步Redis请求
            AsyncRedis::getInstance()->get("key", $promise);
        });
        $promise_group->add("redis_2", function(Promise $promise){
            // 发起异步Redis请求
            AsyncRedis::getInstance()->get("key2", $promise);
        });
        $promise = new Promise();
        $promise->then(function($value){
            $data_1 = $value['redis_1'];
            $data_2 = $value['redis_2'];

            $promise = new Promise();
            // 发起异步Redis请求
            AsyncRedis::getInstance()->get("key3", $promise);
            return $promise;
        })->then(function($value) use ($request, $response) {
            $response->end($value);
        });
        $promise_group->setPromise($promise);
        $promise_group->run();
    }

    public function HttpTest(\swoole_http_request $request, \swoole_http_response $response)
    {
        $promise_group = new PromiseGroup();
        $promise_group->add("http_1", function(Promise $promise){
            // 发起异步Redis请求
            AsyncHttpClient::get("www.baidu.com", "/" , $promise);
        });
        $promise_group->add("http_2", function(Promise $promise){
            // 发起异步Redis请求
            AsyncHttpClient::getByIP("127.0.0.1", "/" , $promise);
        });
        $promise = new Promise();
        $promise->then(function($value){
            $data_1 = $value['http_1'];
            $data_2 = $value['http_2'];

            $promise = new Promise();
            // 发起异步Http请求
            AsyncHttpClient::post("www.test.com", "/" , ['id' => 1], $promise);
            return $promise;
        })->then(function($value) use ($request, $response) {
            $response->end($value);
        });
        $promise_group->setPromise($promise);
        $promise_group->run();
    }


}