<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/12/2
 * Time: 下午9:53
 */
namespace cache;

use base\promise\Promise;

abstract class ILoader
{
    /**
     * @var \swoole_server
     */
    protected $server;

    protected $id;

    protected $data;

    protected $tick = 1;

    protected $count;

    public function broadcast($data)
    {
        $worker_num = $this->server->setting['worker_num'] - 1;
        while( $worker_num >= 0 )
        {
            $this->server->sendMessage(json_encode([
                'id' => $this->id,
                'data' => $data
            ]), $worker_num);
            $worker_num --;
        }
    }

    public function setServer(\swoole_server $server)
    {
        $this->server = $server;
    }

    public function set($data)
    {
        $this->data = $data;
    }

    public function get()
    {
        return $this->data;
    }

    abstract public function load(Promise $promise);

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function refresh()
    {
        $this->count ++;
        if( $this->count >= $this->tick ) {
            $this->count = 0;
            return true;
        }
        return false;
    }

}