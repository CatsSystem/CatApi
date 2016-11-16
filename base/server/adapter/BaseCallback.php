<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/4/8
 * Time: 下午5:43
 */

namespace base\server\adapter;

use base\core\Config;
use base\server\ICallback;



abstract class BaseCallback extends ICallback
{
    public function onClose()
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
        swoole_set_process_name(Config::get('project_name') . " server running master:" . $server->master_pid);
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
        swoole_set_process_name(Config::get('project_name') .' server manager:' . $server->manager_pid);
        if (!empty(Config::getField('project', 'pid_path'))) {
            file_put_contents(Config::getField('project', 'pid_path') . DIRECTORY_SEPARATOR . Config::get('project_name') . '_manager.pid', $server->manager_pid);
        }
    }

    /**
     * @param $server
     * @throws \Exception
     * @desc 服务关闭，删除进程id文件
     */
    public function onManagerStop($server)
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
            swoole_set_process_name(Config::get('project_name') . " server tasker  num: ".($server->worker_id - $workNum)." pid " . $server->worker_pid);
        } else {
            swoole_set_process_name(Config::get('project_name') . " server worker  num: {$server->worker_id} pid " . $server->worker_pid);
        }

        if(function_exists('opcache_reset')) {
            opcache_reset();
        }
    }

    public function onWorkerStop($server, $workerId)
    {
        
    }
}
