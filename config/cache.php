<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/6/15
 * Time: 上午11:29
 */

return array(
    'memcache' => array(
        'connect' => 'socket', // tcp, socket
        'host' => 'unix:///data/ops/mc.socket',
        'backup_host' => 'unix:///data/ops/mc.socket',
    ),
    'redis_single' => array(
        'host' => '172.18.101.7',
        'port' => 6379,
    ),
    'redis_cluster' => array(
        'nodes' => array(
            array(
                'host' => '172.18.25.11',
                'port' => 6916),
            array(
                'host' => '172.18.25.11',
                'port' => 6917),
            array(
                'host' => '172.18.25.12',
                'port' => 6907),
            array(
                'host' => '172.18.25.12',
                'port' => 6908),
            array(
                'host' => '172.18.25.13',
                'port' => 6903),
            array(
                'host' => '172.18.25.13',
                'port' => 6904),
            array(
                'host' => '172.18.25.11',
                'port' => 6920),
            array(
                'host' => '172.18.25.11',
                'port' => 6921),
            array(
                'host' => '172.18.25.12',
                'port' => 6911),
         ),
         'connect_timeout' => 1,
         'read_timeout'    => 1,
         // 3 modes:
         // 'NONE': only send commands to master nodes.
         // 'ERROR': failover for slave nodes if master nodes not reached.
         // 'DISTRIBUTE': random distribute request between master and slave.
         'failover_mode'  => 'ERROR'
    ),
);
