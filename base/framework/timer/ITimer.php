<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/2/9
 * Time: 21:31
 */

namespace base\framework\timer;

abstract class ITimer
{
    protected $params;

    /**
     * 定时器的Key值,用于找到定时器ID
     * @var string
     */
    protected $key;

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function setParams($params)
    {
        $this->params = $params;
    }

    abstract public function doAction();
}