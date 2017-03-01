<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/10/26
 * Time: 下午5:51
 */

namespace base\async\db\adapter;

use base\async\db\Driver;
use base\async\db\Pool;
use base\common\Error;
use base\promise\Promise;
use base\config\Config;

class SwooleMySQL extends Driver
{

    /**
     * @var \swoole_mysql
     */
    private $db;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function connect($reconnect = false, Promise $promise = null, $timeout = 3000)
    {
        if($reconnect)
        {
            $this->close();
        }
        $this->db = new \swoole_mysql();
        $this->db->on('Close', function($db){
            $this->is_close = true;
            Pool::getInstance()->close($this, true);
        });
        $timeId = swoole_timer_after($timeout, function() use ($promise){
            $this->close();
            if ($promise) {
                $promise->reject(null);
            }
        });
        $this->db->connect(Config::get('swoole_mysql'), function($db, $r) use ($promise,$timeId) {
            swoole_timer_clear($timeId);
            if ($r === false) {
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

    /**
     * @throws \Exception
     */
    public function close()
    {
        $this->is_close = true;
        if($this->db)
        {
            $this->db->close();
        }
    }

    public function async_query($sql, Promise $promise, $timeout)
    {
        $get_one    = $sql[1];
        $sql        = $sql[0];
        $timeId = swoole_timer_after($timeout, function() use ($promise, $sql){
            Pool::getInstance()->close($this);
            $promise->resolve([
                'code' => Error::ERR_MYSQL_TIMEOUT,
            ]);
        });
        $this->db->query($sql, function($db, $result) use ($sql, $promise,$timeId, $get_one){
            Pool::getInstance()->close($this);
            swoole_timer_clear($timeId);
            if($result === false) {
                $promise->resolve([
                    'code'  => Error::ERR_MYSQL_QUERY_FAILED,
                    'errno' => $db->errno,
                    'msg'   => sprintf("%s \n [%d] %s",$sql, $db->errno, $db->error)
                ]);
            } else if($result === true) {
                $promise->resolve([
                    'code'          => Error::SUCCESS,
                    'affected_rows' => $db->affected_rows,
                    'insert_id'     => $db->insert_id
                ]);
            } else {
                $promise->resolve([
                    'code'  => Error::SUCCESS,
                    'data'  => empty($result) ? [] : ($get_one ? $result[0] : $result)
                ]);
            }
        });
    }
}