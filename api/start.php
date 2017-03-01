<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/12/2
 * Time: 上午11:13
 */

function usage()
{
    echo "php start.php start | restart | stop | reload [-d]\n";
}

if( !isset($argv[1]) )
{
    usage();
    exit;
}

$cmd = $argv[1];

$debug = ( isset($argv[2]) &&  $argv[2] == '-d' )? 'release' : 'debug';

switch($cmd)
{
    case 'start':
    {
        require_once 'main.php';
        break;
    }
    case 'restart':
    {
        $config = include "config/{$debug}/config.php";
        $pid_path = $config['project']['pid_path'] . '/' . $config['project_name'] . '_master.pid';
        $manager_pid_path = $config['project']['pid_path'] . '/' . $config['project_name'] . '_manager.pid';
        shell_exec("kill -15 `cat {$manager_pid_path}`");
        shell_exec("kill -15 `cat {$pid_path}`");
        sleep(3);
        require_once 'main.php';
        break;
    }
    case 'stop':
    {
        $config = include "config/{$debug}/config.php";
        $pid_path = $config['project']['pid_path'] . '/' . $config['project_name'] . '_master.pid';
        $manager_pid_path = $config['project']['pid_path'] . '/' . $config['project_name'] . '_manager.pid';
        shell_exec("kill -15 `cat {$manager_pid_path}`");
        shell_exec("kill -15 `cat {$pid_path}`");
        break;
    }
    case 'reload':
    {
        $config = include "config/{$debug}/config.php";
        $pid_path = $config['project']['pid_path'] . '/' . $config['project_name'] . '_master.pid';
        $manager_pid_path = $config['project']['pid_path'] . '/' . $config['project_name'] . '_manager.pid';
        shell_exec("kill -15 `cat {$manager_pid_path}`");
        shell_exec("kill -USR1 `cat {$pid_path}`");
        break;
    }
}

