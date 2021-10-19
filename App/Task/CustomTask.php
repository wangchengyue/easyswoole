<?php
namespace App\Task;

use EasySwoole\Task\AbstractInterface\TaskInterface;

class CustomTask implements TaskInterface{

    protected $data;
    public function __construct($data){

        $this->data = $data;

    }
    public function run(int $taskId,int $workerIndex){

    }
    function onException(\Throwable $throwable,int $taskId,int $workerIndex){

    }


}
