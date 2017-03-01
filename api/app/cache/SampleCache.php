<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/12/15
 * Time: 上午12:28
 */
namespace cache;

use base\framework\cache\ILoader;
use common\Constants;
use base\promise\Promise;
use common\Error;

class SampleCache extends ILoader
{
    public function __construct()
    {
        $this->id = Constants::CACHE_SAMPLE;
        $this->tick = 60;                   //  每60个tick更新一次,即600s
    }

    public function load(Promise $promise)
    {
        // 更新缓存数据, 结果使用$promise->resolve()返回

        $promise->resolve([
            'code'  => Error::SUCCESS,
            'data'  => "Hello World"
        ]);
    }
}
