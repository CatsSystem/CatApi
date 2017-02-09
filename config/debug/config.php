<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/6/15
 * Time: 上午10:48
 */

return array(
    'project_name' => 'async_server',

    'app_path' => 'app',
    'ctrl_path' => 'api\\module',
    'callback' => 'server\\WebSocket',


    'project'=>array(
        'pid_path' => '/var/run',
    ),

    'socket' => array(
        'mode'          => SWOOLE_PROCESS,
        'socket_type'   => 'ws',

        'host' => '0.0.0.0',
        'port' => 9501,
        'daemonize' => 0,
        
        // Work Process Config
        'worker_num' => 1,
        'dispatch_mode' => 2,

        'task_worker_num' => 1,
    ),
);
