<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/12/6
 * Time: 下午11:15
 */
namespace base\framework\task;

class Task
{
    protected $_task;
    protected $_method;

    protected $_params;

    public function __construct($data)
    {
        $this->_params = json_decode($data, true);

        $this->_task = $this->_params['task'];
        $this->_method = $this->_params['method'];
    }

    public function getTask()
    {
        return isset($this->_task) ? $this->_task : "";
    }

    public function getMethod()
    {
        return isset($this->_method) ? $this->_method : "";
    }

    public function getParams()
    {
        return isset($this->_params['params']) ? $this->_params['params'] : array();
    }
}