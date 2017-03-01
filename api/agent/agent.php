<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/2/9
 * Time: 21:52
 */

/**
 * 用于接收重启服务器命令的服务
 */
$server = new swoole_http_server("127.0.0.1", 9502, SWOOLE_BASE);
$server->set([
    'worker_num' => 1,
    'daemonize' => 1,
]);

$server->on('WorkerStart', function(\swoole_server $serv) {
    swoole_set_process_name("monitor process");
});

$server->on('Request', function(swoole_http_request $request, swoole_http_response $response) {
    $process = new \Swoole\Process(function($process){
        $process->exec("/usr/bin/php" ,  ["/var/www/project/start.php", "restart", "-d"]);
    });
    $process->daemon(false, false);
    $process->start();
    \Swoole\Process::wait(true);
});