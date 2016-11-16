<?php

namespace base\core;

class Common
{
    public static function createId() {
        return time() . Common::randStr(6);
    }

    public static function randStr($len=6,$format='ALL') {
        $chars='0123456789';

        mt_srand((double)microtime()*1000000*getmypid());
        $password="";
        while(strlen($password)<$len) {
            $password.=substr($chars,(mt_rand()%strlen($chars)),1);
        }
        return $password;
    }
}