<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/1/6
 * Time: 下午3:21
 */

namespace base\socket\adapter;

abstract class Ws extends Http
{
    public function onReceive(\swoole_server $server, $fd, $from_id, $data)
    {
        // TODO: Implement onReceive() method.
    }

    abstract public function onMessage(\swoole_websocket_server $server, \swoole_websocket_frame $frame);

    public function onOpen(\swoole_websocket_server $svr, \swoole_http_request $req)
    {

    }
}