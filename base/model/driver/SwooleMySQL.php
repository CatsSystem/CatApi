<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/10/26
 * Time: 下午5:51
 */

namespace base\model\driver;


use base\model\Driver;
use base\model\Pool;
use GuzzleHttp\Promise\Promise;
use sdk\config\Config;
use server\HttpServer;

;

class SwooleMySQL extends Driver
{

    /**
     * @var \swoole_mysql
     */
    private $db;

    private $is_close = true;

    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function connect($reconnect = false, Promise $promise = null)
    {
        if($reconnect)
        {
            $this->close();
        }
        if( $this->is_close )
        {
            $this->db = new \swoole_mysql();
            $timeId = swoole_timer_after(2000, function() use ($promise){
                $this->close();
                if ($promise) {
                    $promise->reject(null);
                }
            });
            $this->db->connect(Config::get('swoole_mysql'), function($db, $r) use ($promise,$timeId) {
                swoole_timer_clear($timeId);
                if ($r === false) {
                    var_dump($this->id, $db->connect_errno, $db->connect_error);
                    if($promise){
                        $promise->reject($db->connect_error);
                    }
                    return;
                }
                if($promise){
                    $promise->resolve($db);
                }
            });
        }
    }

    public function query($sql, $is_query = false)
    {
        throw new \Exception("Async Driver not support sync query");
    }

    public function close()
    {
        $this->is_close = true;
        try {
            if($this->db)
            {
                $this->db->close();
            }
        }catch (\Exception $e) {
            var_dump($e->getMessage());
        }
    }

    public function async_query($sql, Promise $promise, $is_query = false)
    {
        $timeId = swoole_timer_after(3000, function() use ($promise){
            Pool::getInstance()->close($this);
            $promise->resolve(null);
        });
        $this->db->query($sql, function($db, $result) use ($promise,$timeId){
            Pool::getInstance()->close($this);
            swoole_timer_clear($timeId);
            if($result === false) {
                $promise->reject(null);
            } else if($result === true) {
                $promise->resolve([$db->affected_rows, $db->insert_id]);
            } else {
                $promise->resolve($result);
            }
        });
    }
}