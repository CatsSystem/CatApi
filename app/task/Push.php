<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/2/9
 * Time: 23:01
 */

namespace task;

use common\Error;
use base\task\IRunner;
use server\WebSocket;

class Push extends IRunner
{
    public function push_one($params)
    {
        if( WebSocket::check_ws($params['fd'])) {
            WebSocket::$static_server->push($params['fd'],
                json_encode($params['data'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            return [
                'code' => Error::SUCCESS
            ];
        }
        return [
            'code' => Error::ERR_INVALID_FD
        ];
    }

    public function push_group($params)
    {
        foreach ($params['fd'] as $fd)
        {
            if( WebSocket::check_ws($fd)) {
                WebSocket::$static_server->push($fd,
                    json_encode($params['data'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            }
        }
        return [
            'code' => Error::SUCCESS
        ];
    }

    public function push_all($params)
    {
        foreach (WebSocket::$static_server->connections as $fd)
        {
            if( WebSocket::check_ws($fd)) {
                WebSocket::$static_server->push($fd,
                    json_encode($params['data'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            }
        }
        return [
            'code' => Error::SUCCESS
        ];
    }
}