<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/2/9
 * Time: 22:01
 */

namespace api\module\admin;

use base\framework\BaseController;
use common\Error;

class Console extends BaseController
{
    public function index()
    {

    }

    public function stats()
    {
        $this->request->callback([
            'code'  => Error::SUCCESS,
            'data'  => $this->request->getSocket()->stats()
        ]);
    }

    public function shutdown()
    {
        $this->request->getSocket()->shutdown();
    }
}