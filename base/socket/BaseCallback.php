<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/4/8
 * Time: 下午5:43
 */

namespace base\socket;

use base\cache\CacheLoader;
use base\config\Config;
use base\task\Task;
use base\task\TaskRoute;

abstract class BaseCallback
{

    /**
     * @var \swoole_server
     */
    protected $server;

    public function onClose(\swoole_server $server, $fd, $from_id)
    {

    }
    public function onConnect()
    {

    }

    /**
     * @param $server
     * @throws \Exception
     * @desc 服务启动，设置进程名及写主进程id
     */
    public function onStart($server)
    {
        swoole_set_process_name(Config::get('project_name') . " socket running master:" . $server->master_pid);
        if (!empty(Config::getField('project', 'pid_path'))) {
            file_put_contents(Config::getField('project', 'pid_path') . DIRECTORY_SEPARATOR . Config::get('project_name') . '_master.pid', $server->master_pid);
        }
    }

    /**
     * @throws \Exception
     */
    public function onShutDown()
    {
        if (!empty(Config::getField('project', 'pid_path'))) {
            $filename = Config::getField('project', 'pid_path') . DIRECTORY_SEPARATOR . Config::get('project_name') . '_master.pid';
            if (is_file($filename)) {
                unlink($filename);
            }
            $filename = Config::getField('project', 'pid_path') . DIRECTORY_SEPARATOR . Config::get('project_name') . '_manager.pid';
            if (is_file($filename)) {
                unlink($filename);
            }
        }
    }

    /**
     * @param $server
     * @throws \Exception
     * @desc 服务启动，设置进程名
     */
    public function onManagerStart($server)
    {
        swoole_set_process_name(Config::get('project_name') .' socket manager:' . $server->manager_pid);
        if (!empty(Config::getField('project', 'pid_path'))) {
            file_put_contents(Config::getField('project', 'pid_path') . DIRECTORY_SEPARATOR . Config::get('project_name') . '_manager.pid', $server->manager_pid);
        }
    }

    public function onManagerStop()
    {
        if (!empty(Config::getField('project', 'pid_path'))) {
            $filename = Config::getField('project', 'pid_path') . DIRECTORY_SEPARATOR . Config::get('project_name') . '_manager.pid';
            if (is_file($filename)) {
                unlink($filename);
            }
        }
    }

    public function onWorkerStart($server, $workerId)
    {
        $workNum = Config::getField('socket', 'worker_num');
        if ($workerId >= $workNum) {
            swoole_set_process_name(Config::get('project_name') . " socket tasker  num: ".($server->worker_id - $workNum)." pid " . $server->worker_pid);
        } else {
            swoole_set_process_name(Config::get('project_name') . " socket worker  num: {$server->worker_id} pid " . $server->worker_pid);
        }
    }

    public function onWorkerStop($server, $workerId)
    {
        
    }

    public function setServer(\swoole_server $server)
    {
        $this->server = $server;
    }

    /**
     * @return \swoole_server
     */
    public function getServer()
    {
        return $this->server;
    }

    abstract public function before_start();

    public function onTask(\swoole_server $server, $task_id, $from_id, $data)
    {
        $task = new Task($data);
        $result = TaskRoute::route($task);
        return $result;
    }

    public function onFinish(\swoole_server $serv, $task_id, $data)
    {

    }

    public function onPipeMessage(\swoole_server $server, $from_worker_id, $message)
    {
        $data = json_decode($message, true);
        if( $data['type'] == 'cache' )
        {
            CacheLoader::getInstance()->set($data['id'], $data['data']);
        }
        return;
    }

    /**
     * @param \swoole_server $server
     * @param $fd           int
     * @param $from_id      int
     * @param $data         string
     * @return mixed
     */
    abstract public function onReceive(\swoole_server $server, $fd, $from_id, $data);

}
