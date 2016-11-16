<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/6/15
 * Time: 上午11:08
 */

namespace api;

use base\promise\PromiseGroup;
use GuzzleHttp\Promise\Promise;

class Api
{
    /**
     * @param \swoole_http_request $request
     * @param \swoole_http_response $response
     * @return array|string
     * @throws \Exception
     */
    public static function playurl(\swoole_http_request $request, \swoole_http_response $response)
    {
        $params = $request->get;
        $promise_group = new PromiseGroup();
        $promise_group->add("count" , function(Promise $promise) use($params) {
            $promise->resolve(true);
        });
        $promise_group->add("ip" , function(Promise $promise) use($params, $response){
            $promise->resolve("");
        });
        $cid_promise = new Promise();
        $cid_promise->then(function($value) use($response, $params) {
            $promise = new Promise();
            VideoLoad::load_by_cid($params, $promise);
            return $promise;
        })->then(function($video_info) use($params,$request, $response){
            $promise = new Promise();
            VideoLoad::load_dp($params, $promise);
            return $promise;
        })->then(function($video_info) use ($response){
            $response->end(json_encode($video_info, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        });
        $promise_group->setPromise($cid_promise);
        $promise_group->run();
        return -1;
    }
}
