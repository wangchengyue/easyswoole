<?php
namespace App\Tools;


use EasySwoole\Component\Singleton;

use EasySwoole\Session\Session as ParSession;

class Session extends ParSession{

    use Singleton;
}
