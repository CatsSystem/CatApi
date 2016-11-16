<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/4/8
 * Time: 下午6:04
 */
namespace base\server;

interface IController
{

    /**
     * 业务逻辑开始前执行
     */
    public function _before();

    /**
     * 业务逻辑结束后执行
     */
    public function _after();
}