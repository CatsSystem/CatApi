<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/6/15
 * Time: 上午11:08
 */

namespace api\module\home;

use base\async\cache\AsyncRedis;
use base\framework\BaseController;
use base\model\MySQLStatement;
use base\promise\PromiseGroup;
use base\Promise\Promise;
use cache\CacheLoader;
use common\Constants;
use common\Error;

class Api extends BaseController
{
    public function testApi()
    {
        // $this->request 请求对象
        // $this->params 请求参数数组

        $promise = new Promise();
        $promise->then(function() {
            $promise = new Promise();
            // Async Task
            $this->sendTask('SampleTask', 'sample_task',[
                'data' => 'Hello'
            ], function($result) use ($promise) {
                $promise->resolve($result['data']);
            });

            return $promise;
        })->then(function($result) {
            // save global data
            $this->global_data['async_task'] = $result;

            $promise = new Promise();

            // Async MySQL Query
            MySQLStatement::prepare()
                ->select("Test", "*")->where([
                    'id'    => $this->params['id']
                ])->getOne($promise);

            return $promise;
        })->then(function($result) {
            if($result['code'] != Error::SUCCESS) {
                var_dump($result['msg']);
                $this->request->callback([
                    'code' => $result['code']
                ], true);
            }

            $promise = new Promise();
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
            $promise_group = new PromiseGroup();
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

            $promise_group->add("cache", function(Promise $promise) {
                AsyncRedis::getInstance()->get("cache", $promise);
            }, $handle);

            $promise_group->add("db", function(Promise $promise) {
                MySQLStatement::prepare()->select("Test", "*")->where([
                    'id'    => $this->params['id']
                ])->getOne($promise);
            }, $handle);
            $promise = new Promise();
            $promise_group->setPromise($promise);
            $promise_group->run();
            return $promise;
        })->then(function($result) {
            var_dump($result);
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
        $this->request->callback([
            'code' => Error::SUCCESS,
            'data'  => CacheLoader::getInstance()->get(Constants::CACHE_SAMPLE)
        ]);
    }
}
