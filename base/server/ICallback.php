<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/4/8
 * Time: 下午5:30
 */

namespace base\server;

abstract class ICallback
{
    abstract public function onRequest(\swoole_http_request $request, \swoole_http_response $response);
    abstract public function onStart($server);
    abstract public function onShutdown();
    abstract public function onClose();
    abstract public function onConnect();

    abstract public function setServer($server);
    abstract public function beforeStart(\swoole_server $server);
}