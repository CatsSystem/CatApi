<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/6/15
 * Time: 上午11:26
 */

namespace base\cache;

use base\core\Config;

class Memcache
{
    private static $instance = null;

    /**
     * @return Memcache
     */
    public static function getInstance()
    {
        if(Memcache::$instance == null)
        {
            Memcache::$instance = new Memcache();
        }
        return Memcache::$instance;
    }

    var $mmcache_available;
    var $memcache;
    var $memc_connect = false;
    var $server;
    function __construct()
    {
        $this->mmcache_available = false;
        $this->server = Config::getField('memcache', 'host');
    }

    /**
     * 连接到Memcache服务器
     * @return boolean 是否成功
     */
    public function connect()
    {
        if (class_exists("Memcached"))
        {
            $this->memcache = new \Memcached;
            // $this->mmcache_available = $this->memcache->pconnect('unix:///socks/memcached.sock',0);
            $this->mmcache_available = $this->memcache->addServer($this->server, 11211);
            if (!$this->mmcache_available && defined("MEMCACHE_BACKUP_SERVER"))
            {
                $this->server = Config::getField('memcache','backup_host');
                $this->mmcache_available = $this->memcache->addServer(Config::getField('memcache','backup_host'), 11211);
            }
        }
        return $this->mmcache_available;
    }

    /**
     * 获取Key
     * @param string $key Key名
     * @return string 结果
     */
    public function get($key)
    {
        if (!$this->mmcache_available && !$this->connect()) return false;
        return $this->memcache->get($key);
    }

    /**
     * 写入数据至Key
     * @param string $key Key名
     * @param string $var 数据
     * @param int $expires 超时（秒）
     * @param int $flag FLAG
     * @return bool 成功返回TRUE 失败返回FALSE
     */
    public function set($key,$var,$expires=60,$flag=0)
    {
        if (!$this->mmcache_available && !$this->connect()) return false;
        return $this->memcache->set($key,$var,$flag,$expires);
    }

    /**
     * 删除一个Key
     * @param string $key Key名
     * @param int $timeout This deprecated parameter is not supported, and defaults to 0 seconds. Do not use this parameter.
     * @return bool 成功返回TRUE 失败返回FALSE
     */
    public function delete($key,$timeout=0)
    {
        if (!$this->mmcache_available && !$this->connect()){
            return false;
        }
        return $this->memcache->delete($key,$timeout);
    }

    /**
     * 获取服务器统计信息
     * @return array 返回一个服务器信息的二维数组或失败时返回FALSE
     */
    public function getExtendedStats()
    {
        if (!$this->mmcache_available && !$this->connect()) return false;
        return $this->memcache->getExtendedStats();
    }

    /**
     * 删除服务器所有缓存数据
     * @return bool 成功返回TRUE 失败返回FALSE
     */
    public function flush()
    {
        if (!$this->mmcache_available && !$this->connect()) return false;
        return $this->memcache->flush();
    }

    /**
     * 使用锁
     * @param string $key 锁KEY
     * @param int $lock_expires 锁定最长时间
     * @param int $timeout 锁超时
     * @return 获取锁成功返回TRUE 否则返回FALSE
     */
    public function lock($key, $lock_expires = 180, $timeout = 3)
    {
        if (!$this->mmcache_available && !$this->connect()) return false;
        $start = microtime(true);
        while ($this->memcache->add("mutex:".$key, "lock", 0, $lock_expires) === false)
        {
            usleep(1000);	// sleep 1ms
            if (microtime(true)-$start > $timeout) return false;
        }
        return true;
    }

    /**
     * 解锁
     * @param string $key 锁KEY
     * @return void
     */
    public function unlock($key)
    {
        if (!$this->mmcache_available && !$this->connect()) return false;
        $this->memcache->delete("mutex:".$key, 0);
    }

    /**
     * 添加到堆栈
     * @param string $key 堆栈名
     * @param mixed $val 添加的数据
     * @param int $expires 超时
     * @return void
     */
    public function push($key, $val, $expires=60)
    {
        if (!$this->mmcache_available && !$this->connect()) return false;
        if (!$this->lock("stack:".$key)) return false;
        $index = $this->increment("stack:".$key);
        $this->set("stack:".$key.":".$index, $val, 0, $expires);
        $this->unlock("stack:".$key);
    }

    /**
     * 从堆栈中取出一个数据
     * @param string $key 堆栈名
     * @return mixed 取出的数据
     */
    public function pop($key)
    {
        if (!$this->mmcache_available && !$this->connect()) return false;
        if (!($index = $this->memcache->get("stack:".$key))) return null;
        if (!$this->lock("stack:".$key)) return false;
        $this->decrement("stack:".$key);
        $val = $this->get("stack:".$key.":".$index);
        $this->unlock("stack:".$key);
        return $val;
    }

    /**
     * 添加到队列
     * @param string $key 队列名
     * @param mixed $val 添加的数据
     * @param int $expires 超时
     * @return void
     */
    public function enqueue($key, $val, $expires=60)
    {
        if (!$this->mmcache_available && !$this->connect()) return false;
        if (!$this->lock("queue:".$key,180,10)) return false;
        $num = $this->increment("queue:item:".$key);
        if ($num == 0)
        {
            $this->set("queue:first:".$key,0,0);
        }
        $index = $this->increment("queue:last:".$key);
        $this->set("queue:data:".$key.":".$index, $val, 0, $expires);
        $this->unlock("queue:".$key);
    }

    /**
     * 从队列中取出一个数据
     * @param string $key 队列名
     * @return mixed 取出的数据
     */
    public function dequeue($key)
    {
        if (!$this->mmcache_available && !$this->connect()) return false;
        if (!($num = $this->memcache->get("queue:item:".$key))) return null;
        if (!$this->lock("queue:".$key,180,10)) return false;
        $index = $this->increment("queue:first:".$key);
        $this->decrement("queue:item:".$key);
        $val = $this->get("queue:data:".$key.":".$index);

        if ($num == 1){
            $this->delete("queue:first:".$key);
            $this->delete("queue:last:".$key);
            $this->delete("queue:item:".$key);
        }
        $this->unlock("queue:".$key);
        return $val;
    }

    /**
     * 关闭连接
     * @return void
     */
    public function close()
    {
        if ($this->mmcache_available)
        {
            $this->memcache->close();
            $this->mmcache_available=false;
        }
    }

    /**
     * 增加KEY中数据的数字
     * @param string $key KEY名
     * @param int $value 每次增加的数量 （默认：1）
     * @param int $lifetime 不存在时设置的超时 （默认：0）
     * @return int 添加后的数字
     */
    public function increment($key,$value=1,$lifetime=0)
    {
        if (!$this->mmcache_available && !$this->connect()) return false;
        $v = $this->memcache->increment($key,$value);
        if ($v === false)
        {
            $this->memcache->set($key,$value,0,$lifetime);
            return $value;
        }else
        {
            return $v;
        }
    }

    /**
     * 减少KEY中数据的数字
     * @param string $key KEY名
     * @param int $value 每次减少的数量 （默认：1）
     * @return int 减少后的数字
     */
    public function decrement($key,$value=1)
    {
        if (!$this->mmcache_available && !$this->connect()) return false;
        return $this->memcache->decrement($key,$value);
    }
}
