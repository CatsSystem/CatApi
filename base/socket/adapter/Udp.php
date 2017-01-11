<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/1/6
 * Time: 下午3:21
 */

namespace base\socket\adapter;

use base\socket\BaseCallback;

abstract class Udp extends BaseCallback
{
    public function onReceive(\swoole_server $server, $fd, $from_id, $data)
    {
        // TODO: Implement onReceive() method.
    }

    /**
     * @param \swoole_server $server
     * @param $data             string
     * @param $client_info      array
     * @return mixed
     */
    abstract public function onPacket(\swoole_server $server, $data, $client_info);

    protected function send($client_info, $data)
    {
        $this->server->sendto($client_info['address'], $client_info['port'], $data, $client_info['server_socket']);
    }
}