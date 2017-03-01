<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/10/26
 * Time: 下午6:53
 */

namespace base\async\http;

use base\common\Error;
use base\promise\Promise;

/**
 * 异步Http客户端封装
 * Class AsyncHttpClient
 * @package base\async\http
 */
class AsyncHttpClient
{
    /**
     * @var \swoole_http_client
     */
    private $http_client;

    /**
     * 待请求的域名
     * @var string
     */
    private $domain;

    /**
     * 是否是https请求
     * @var bool
     */
    private $is_ssl;

    /**
     * 端口号
     * @var int
     */
    private $port;

    public function __construct($domain, $is_ssl = false, $port = 80)
    {
        $this->domain = $domain;
        $this->is_ssl = $is_ssl;

        if( $is_ssl && $port = 80 ) {
            $port = 443;
        }
        $this->port = $port;
    }

    /**
     * @param Promise $promise 用于承载异步请求的结果
     * @return bool 若无需DNS查询,返回true;否则返回false
     */
    public function init(Promise $promise = null)
    {
        // 利用ip2long方法检测传入的是否为IP
        if( !ip2long($this->domain) ) {   // 传入的是域名
            swoole_async_dns_lookup($this->domain, function ($host, $ip) use ($promise){
                $this->domain = $ip;
                $this->http_client = new \swoole_http_client($this->domain, $this->port, $this->is_ssl);
                if( $promise ) {
                    $promise->resolve(Error::SUCCESS);
                }
            });
            return false;
        } else {
            $this->http_client = new \swoole_http_client($this->domain, $this->port, $this->is_ssl);
            return true;
        }
    }

    public function get($path, Promise $promise,  $timeout = 3000)
    {
        $timeId = swoole_timer_after($timeout, function() use ($promise){
            $this->http_client->close();
            $promise->resolve([
                'code'  => Error::ERR_HTTP_TIMEOUT
            ]);
        });
        $this->http_client->get($path, function($cli) use($promise,$timeId){
            \swoole_timer_clear($timeId);
            $this->http_client->close();
            $promise->resolve([
                'code'      => Error::SUCCESS,
                'data'      => $cli->body,
                'status'    => $cli->statusCode
            ]);
        });
    }

    public function post($path, $data, Promise $promise, $timeout = 3000)
    {
        $timeId = swoole_timer_after($timeout, function() use ($promise){
            $this->http_client->close();
            $promise->resolve([
                'code'  => Error::ERR_HTTP_TIMEOUT
            ]);
        });
        $this->http_client->post($path, $data, function($cli) use($promise,$timeId){
            \swoole_timer_clear($timeId);
            $this->http_client->close();
            $promise->resolve([
                'code'      => Error::SUCCESS,
                'data'      => $cli->body,
                'status'    => $cli->statusCode
            ]);
        });
    }

    public function execute($path, Promise $promise, $timeout = 3000)
    {
        $timeId = swoole_timer_after($timeout, function() use ($promise){
            $this->http_client->close();
            $promise->resolve([
                'code'  => Error::ERR_HTTP_TIMEOUT
            ]);
        });
        $this->http_client->execute($path, function($cli) use($promise,$timeId){
            \swoole_timer_clear($timeId);
            $promise->resolve([
                'code'      => Error::SUCCESS,
                'data'      => $cli->body,
                'status'    => $cli->statusCode
            ]);
        });
    }

    public function cookie()
    {
        return $this->http_client->cookies;
    }

    public function close()
    {
        $this->http_client->close();
    }

    public function __call($name, $arguments)
    {
        if($name == 'get' || $name == 'post' || $name == 'execute' ) {
            return false;
        }
        return call_user_func_array([$this->http_client, $name], $arguments);
    }
}