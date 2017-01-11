<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/1/5
 * Time: 下午4:03
 */

namespace task;

use common\Error;
use base\task\IRunner;

class SampleTask extends IRunner
{
    public function sample_task($params)
    {
        //TODO: 实现任务逻辑
        return [
            'code' => Error::SUCCESS,
            'data' => "task data"
        ];
    }
}