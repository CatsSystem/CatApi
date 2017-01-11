<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/1/6
 * Time: 下午3:21
 */

namespace base\socket\adapter;

use base\socket\BaseCallback;

abstract class Http extends BaseCallback
{
    public function onReceive(\swoole_server $server, $fd, $from_id, $data)
    {
        // TODO: Implement onReceive() method.
    }
    
    abstract public function onRequest(\swoole_http_request $request, \swoole_http_response $response);
}