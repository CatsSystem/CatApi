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

class AsyncHttpClient
{

    public static function get($domain, $url, Promise $promise, $is_https = false, $headers = [])
    {
        $tmp_promise = new Promise();
        $tmp_promise->then(function($host) use($promise,$domain,$url,$headers, $is_https){
            $client = new \swoole_http_client($host, $is_https ? "443" :"80", $is_https);
            $headers['Host'] = $domain;
            $client->setHeaders($headers);
            $timeId = swoole_timer_after(3000, function() use ($client, $promise){
                $client->close();
                $promise->resolve([
                    'code'  => Error::ERR_HTTP_TIMEOUT
                ]);
            });
            $client->get($url, function($cli) use($promise,$timeId,$client){
                \swoole_timer_clear($timeId);
                $client->close();
                $promise->resolve([
                    'code' => Error::SUCCESS,
                    'data' => $cli->body
                ]);
            });
        });
        swoole_async_dns_lookup($domain, function ($host, $ip) use($tmp_promise) {
            $tmp_promise->resolve($ip);
        });
    }

    public static function getByIP($ip, $url, Promise $promise, $is_https = false, $headers = [])
    {
        $client = new \swoole_http_client($ip, $is_https ? "443" :"80", $is_https);
        $client->setHeaders($headers);
        $timeId = swoole_timer_after(3000, function() use ($client, $promise){
            $client->close();
            $promise->resolve([
                'code'  => Error::ERR_HTTP_TIMEOUT
            ]);
        });
        $client->get($url, function($cli) use($promise,$timeId,$client){
            \swoole_timer_clear($timeId);
            $client->close();
            $promise->resolve([
                'code' => Error::SUCCESS,
                'data' => $cli->body
            ]);
        });
    }

    public static function post($domain, $url, $data, Promise $promise, $is_https = false, $headers = [])
    {
        $tmp_promise = new Promise();
        $tmp_promise->then(function($host) use($promise,$domain,$url,$data,$headers, $is_https){
            $client = new \swoole_http_client($host, $is_https ? "443" :"80", $is_https);
            $headers['Host'] = $domain;
            $client->setHeaders($headers);
            $timeId = swoole_timer_after(3000, function() use ($client, $promise){
                $client->close();
                $promise->resolve([
                    'code'  => Error::ERR_HTTP_TIMEOUT
                ]);
            });
            $client->post($url, $data, function($cli) use($promise,$timeId,$client){
                \swoole_timer_clear($timeId);
                $client->close();
                $promise->resolve([
                    'code' => Error::SUCCESS,
                    'data' => $cli->body
                ]);
            });
        });
        swoole_async_dns_lookup($domain, function ($host, $ip) use($tmp_promise) {
            $tmp_promise->resolve($ip);
        });
    }

    public static function postByIP($ip, $url, $data, Promise $promise, $is_https = false, $headers = [])
    {
        $client = new \swoole_http_client($ip,  $is_https ? "443" :"80", $is_https);
        $client->setHeaders($headers);
        $timeId = swoole_timer_after(3000, function() use ($client, $promise){
            $client->close();
            $promise->resolve([
                'code'  => Error::ERR_HTTP_TIMEOUT
            ]);
        });
        $client->post($url, $data, function($cli) use($promise,$timeId,$client){
            \swoole_timer_clear($timeId);
            $client->close();
            $promise->resolve([
                'code' => Error::SUCCESS,
                'data' => $cli->body
            ]);
        });
    }
}