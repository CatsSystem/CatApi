<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/2/9
 * Time: 21:33
 */

namespace timer;

use base\framework\timer\ITimer;

class SampleTimer extends ITimer
{

    public function doAction()
    {
        var_dump($this->params);
    }
}