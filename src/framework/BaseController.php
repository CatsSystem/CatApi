<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/1/5
 * Time: 下午3:26
 */

namespace base\framework;

use base\protocol\Request;
use core\common\Error;

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

    protected function check()
    {
        $list = func_get_args();

        foreach ($list as $name)
        {
            if( !isset($this->params[$name]) )
            {
                return false;
            }
        }
        return true;
    }

    protected function success($data = '')
    {
        if( $data === '' )
        {
            return [
                'code' => Error::SUCCESS
            ];
        }
        return [
            'code' => Error::SUCCESS,
            'data' => $data
        ];
    }

    protected function error($errCode, $errMsg = '')
    {
        return [
            'code' => $errCode,
            'msg' => $errMsg
        ];
    }
}