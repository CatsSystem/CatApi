<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/1/6
 * Time: 下午3:21
 */

namespace base\socket\adapter;

use base\socket\BaseCallback;

abstract class Ws extends BaseCallback
{
    public function onReceive(\swoole_server $server, $fd, $from_id, $data)
    {
        // TODO: Implement onReceive() method.
    }

    abstract public function onMessage(\swoole_server $server, \swoole_websocket_frame $frame);

    public function onOpen(\swoole_websocket_server $svr, \swoole_http_request $req)
    {

    }
}