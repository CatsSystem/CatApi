<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 17/1/11
 * Time: 下午2:48
 */

namespace common;


class Error extends \base\common\Error
{
    // 自定义的错误码, int类型,均为负值, 前50位为框架预留错误码,不要使用

    const ERR_INVALID_FD            = -1001;
    
}