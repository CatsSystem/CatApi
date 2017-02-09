<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/2/9
 * Time: 21:31
 */

namespace base\timer;

abstract class ITimer
{
    protected $params;

    public function __construct()
    {

    }

    public function setParams($params)
    {
        $this->params = $params;
    }

    abstract public function doAction();
}