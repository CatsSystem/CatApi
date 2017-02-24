<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/6/15
 * Time: 上午11:08
 */

namespace api\module\home;

use base\async\cache\AsyncRedis;
use base\async\http\AsyncHttpClient;
use base\framework\BaseController;
use base\model\MySQLStatement;
use base\promise\PromiseGroup;
use base\Promise\Promise;
use base\timer\Timer;
use cache\CacheLoader;
use common\Constants;
use common\Error;

class Api extends BaseController
{
    /**
     * @var AsyncHttpClient
     */
    private $http;

    public function testApi()
    {
        // $this->request 请求对象
        // $this->params 请求参数数组

        // 开始处理逻辑, 固定创建一个Promise对象
        $promise = new Promise();
        $promise->then(function() {

            // 发起一次异步任务请求
            $promise = new Promise();
            // Async Task
            $this->sendTask('SampleTask', 'sample_task',[
                'data' => 'Hello'
            ], function($result) use ($promise) {
                // 在这里处理异步任务的返回结果,并将最终的数据通过Promise发送出去
                $promise->resolve($result['data']);
            });

            return $promise;
        })->then(function($result) {
            // 保存全局数据, 用于后续的逻辑
            $this->global_data['async_task'] = $result;

            // 发起异步MySQL请求
            $promise = new Promise();

            // 最后的getOne方法用于获取单个结果的请求,多个结果或者更新操作使用query方法
            MySQLStatement::prepare()
                ->select("Test", "*")->where([
                    'id'    => $this->params['id']
                ])->getOne($promise);

            return $promise;
        })->then(function($result) {
            if($result['code'] != Error::SUCCESS) {
                var_dump($result['msg']);
                // 返回结果, 支持字符串或数组
                // 第二个参数返回true代表结束当前流程, 直接返回客户端
                $this->request->callback([
                    'code' => $result['code']
                ], true);
            }

            $promise = new Promise();
            // 异步Redis请求, 方法名称对应Redis命令
            AsyncRedis::getInstance()->set("cache", json_encode($result['data']), $promise);

            return $promise;
        })->then(function($result) {
            if($result['code'] != Error::SUCCESS) {
                $this->request->callback([
                    'code' => $result['code']
                ], true);
            }

            $this->request->callback([
                'code'  => Error::SUCCESS,
                'data'  => $this->global_data['async_task']
            ]);
        });
        $promise->resolve(0);
    }

    public function testMulti()
    {
        $promise = new Promise();
        $promise->then(function() {

            // 发起一次并行请求
            $promise_group = new PromiseGroup();
            // 并行请求结果的处理函数, $success代表请求是否成功
            $handle = function($success, $data) {
                if( $success ) {    // fulfilled
                    if( $data['code'] != Error::SUCCESS )
                    {
                        return -1;
                    }
                    return $data['data'];
                } else {            // reject
                    return $data;
                }
            };
            // 每调用一次add,代表添加一次请求, 第一个参数为请求的key值,用于获取请求结果
            $promise_group->add("cache", function(Promise $promise) {
                AsyncRedis::getInstance()->get("cache", $promise);
            }, $handle);

            $promise_group->add("db", function(Promise $promise) {
                MySQLStatement::prepare()->select("Test", "*")->where([
                    'id'    => $this->params['id']
                ])->getOne($promise);
            }, $handle);
            // 固定格式
            $promise = new Promise();
            $promise_group->setPromise($promise);
            $promise_group->run();
            return $promise;
        })->then(function($result) {
            var_dump($result);
            // $result中保存了上面并行请求的结果
            if( !empty($result['cache']) ) {
                $this->request->callback([
                    'code' => Error::SUCCESS,
                    'data' => json_decode($result['cache'], true)
                ], true);
            }

            if( empty($result['db']) ) {
                $this->request->callback([
                    'code' => Error::ERR_NO_DATA,
                ], true);
            }

            $this->request->callback([
                'code' => Error::SUCCESS,
                'data' => $result['db']
            ]);
        });
        $promise->resolve(0);
    }

    public function testCache()
    {
        // 内存缓存 通过ID获取
        CacheLoader::getInstance()->get(Constants::CACHE_SAMPLE);

        $this->request->callback([
            'code' => Error::SUCCESS,
            'data'  => CacheLoader::getInstance()->get(Constants::CACHE_SAMPLE)
        ]);
    }

    public function testHttp()
    {
        $promise = new Promise();
        $promise->then(function() {
            $promise = new Promise();
            // 初始化HttpClient对象 通过保存在Controller的成员变量中使用
            $this->http = new AsyncHttpClient("www.baidu.com");
            $this->http->init($promise);
            return $promise;
        })->then(function($result) {
            if($result != Error::SUCCESS) {
                $this->request->callback([
                    'code' => $result
                ], true);
            }
            $promise = new Promise();
            // 发送http请求
            $this->http->get('/', $promise);

            return $promise;
        })->then(function($result) {
            $this->request->callback($result);
        });

        $promise->resolve(0);
    }

    public function testTimer()
    {
        // 临时定时器
        Timer::after(5000, 'Cancel', [
            'ms'    => 1000,
            'name'  => 'SampleTimer',
            'key'   => 'key'
        ]);

        // 永久定时器
        Timer::tick(1000, 'SampleTimer', 'key', [
            'data' => 'Hello'
        ]);
        $this->request->callback("");
    }
}
