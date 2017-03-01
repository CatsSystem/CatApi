<?php
/**
 * Created by PhpStorm.
 * User: lancelot
 * Date: 16-11-27
 * Time: 下午8:44
 */

use base\Enterance;

require "../vendor/autoload.php";

global $debug;
Enterance::run(__DIR__, $debug);