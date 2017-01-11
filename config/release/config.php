<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/6/15
 * Time: 上午10:48
 */

return array(
    'project_name' => 'async_server',

    'app_path' => 'apps',
    'ctrl_path' => 'ctrl',
    'callback' => 'socket\\HttpServer',


    'project'=>array(
        'default_ctrl' => 'Main',
        'default_method' => 'main',
        'pid_path' => '/var/run',
    ),

    'socket' => array(
        'host' => '0.0.0.0',
        'port' => 9501,
        'daemonize' => 0,
        
        // Work Process Config
        'worker_num' => 1,
        'dispatch_mode' => 3,
        'max_request' => 300000,

        'task_worker_num' => 1,
    ),
);
