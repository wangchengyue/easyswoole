<?php
namespace App\Utility;

use EasySwoole\Component\Process\AbstractProcess;

use EasySwoole\Queue\Job;


class QueueProcess extends AbstractProcess{


    protected function run($arg){
        go(function(){
             MyQUeue::getInstance()->consumer()->listen(function(Job $job){
                 var_dump($job);
             });
        });
    }
}
