<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/2/27
 * Time: 13:45
 */

return [
    /*********************** Redis Server Config ****************************/
    'redis' => array(
        'host'      => '127.0.0.1',
        'port'      => 6379,
        'select'    => 0,
        'pwd'       => 'ky1024'
    ),
    /*********************** Redis Server Config end ************************/

    /*********************** Log Config ****************************/
    'log'=>array(
        'open_log' => true,
        'adapter' => 'File',
        'log_level' => 0,
        'path' => __DIR__ . "/../../log/",
    ),
    /*********************** Log Config end ************************/

    /*********************** MySQL Config ****************************/
    'mysql_pool_count' => 5,

    'swoole_mysql' => array(
        'host' => '127.0.0.1',
        'user' => 'root',
        'password' => '123456',
        'database' => 'Test',
    ),
    /*********************** MySQL Config end ************************/
    
    
];