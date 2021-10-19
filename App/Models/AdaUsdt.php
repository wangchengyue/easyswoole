<?php
namespace App\Models;


use EasySwoole\ORM\AbstractModel;
use EasySwoole\ORM\Utility\Schema\Table;

class AdaUsdt extends AbstractModel {


    protected $tableName="ada_trade";

    public function getTableInfo(bool $isCache= true){
        $table = new Table($this->tableName);
        var_dump($table);
        return $table;
    }
}
