<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/1/5
 * Time: 下午3:26
 */

namespace base\framework;

use base\protocol\Request;

class BaseController
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var array
     */
    protected $params;

    public function before(Request &$request)
    {
        $this->request = $request;
        $this->params = $request->getParams();
        return true;
    }
}