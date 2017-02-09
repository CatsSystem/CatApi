<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/7/8
 * Time: 下午3:03
 */

namespace base\sync\db;

use base\config\Config;

class BaseDB
{
    private static $instance = null;

    /**
     * @return BaseDB
     */
    public static function getInstance()
    {
        if(BaseDB::$instance == null)
        {
            BaseDB::$instance = new BaseDB();
        }
        return BaseDB::$instance;
    }

    private $config;
    private $pdo;

    public function __construct()
    {
        $this->config = Config::get('swoole_mysql');
        $this->pdo = $this->connect();
    }

    /**
     * @param $sql
     * @return \PDOStatement
     * @throws \Exception
     */
    public function query($sql)
    {
        try {
            return $this->run($sql);
        } catch(\Exception $e) {
            if ($e->getCode() == 'HY000') { //just try reconnect.
                try{
                    $this->pdo = $this->connect();
                    return $this->run($sql);
                } catch(\Exception $e) {
                    throw $e;
                }
            }
            return null;
        }
    }

    private function run($sql)
    {
        $statement = $this->pdo->prepare($sql);
        if( $statement->execute() )
        {
            return $statement;
        }
        return null;
    }

    public function last_id($key = "")
    {
        if( $key ) {
            return $this->pdo->lastInsertId($key);
        } else {
            return $this->pdo->lastInsertId();
        }

    }

    public function connect()
    {
        $dbHost = $this->config['host'];
        $dbUser = $this->config['user'];
        $dbPwd  = $this->config['password'];
        $dbName = $this->config['database'];

        $dsn = "mysql:host={$dbHost};port=3306;dbname={$dbName}";
        return new \PDO($dsn, $dbUser, $dbPwd, array(
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8;",
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_PERSISTENT => true
        ));
    }
}