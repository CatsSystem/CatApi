<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/10/26
 * Time: 下午6:53
 */

namespace base\async;


use GuzzleHttp\Promise\Promise;

class AsyncHttpClient
{

    public static function get($domain, $url, Promise $promise, $headers = [])
    {
        $tmp_promise = new Promise();
        $tmp_promise->then(function($host) use($promise,$domain,$url,$headers){
            $client = new \swoole_http_client($host, "80");
            $headers['Host'] = $domain;
            $client->setHeaders($headers);
            $timeId = swoole_timer_after(3000, function() use ($client, $promise){
                $client->close();
                $promise->resolve(null);
            });
            $client->get($url, function($cli) use($promise,$timeId,$client){
                \swoole_timer_clear($timeId);
                $client->close();
                $promise->resolve($cli->body);
            });
        });
        swoole_async_dns_lookup($domain, function ($host, $ip) use($tmp_promise) {
            $tmp_promise->resolve($ip);
        });
    }

    public static function getByIP($ip, $url, Promise $promise, $headers = [])
    {
        $client = new \swoole_http_client($ip, "80");
        $client->setHeaders($headers);
        $timeId = swoole_timer_after(3000, function() use ($client, $promise){
            $client->close();
            $promise->resolve(null);
        });
        $client->get($url, function($cli) use($promise,$timeId,$client){
            \swoole_timer_clear($timeId);
            $client->close();
            //var_dump($cli->body);
            $promise->resolve($cli->body);
        });
    }

    public static function post($domain, $url, $data, $promise)
    {
        swoole_async_dns_lookup($domain, function ($host, $ip) use($promise) {
            $promise->resolve($ip);
        });
    }
}