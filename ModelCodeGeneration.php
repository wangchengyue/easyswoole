<?php
include __DIR__."/vendor/autoload.php";

// EasySwoole Core核心
\EasySwoole\EasySwoole\Core::getInstance()->initialize();

//Model生成实例
//http://www.easyswoole.com/Components/codeGeneration_2.x.html
go(function () {
    $mysqlConfig = new \EasySwoole\ORM\Db\Config(\EasySwoole\EasySwoole\Config::getInstance()->/**/getConf('MYSQL'));
    //获取连接
    $connection = new \EasySwoole\ORM\Db\Connection($mysqlConfig);
    $tableName = 'eb_ada_trade';
    $tableName = 'eb_system_log';
    //获取数据表结构对象
    $tableObjectGeneration = new \EasySwoole\ORM\Utility\TableObjectGeneration($connection, $tableName);
    $schemaInfo = $tableObjectGeneration->generationTable();

    $tablePre = 'eb_';//表前缀
    $path = "App\\Model";
    $extendClass = \EasySwoole\ORM\AbstractModel::class;
    $modelConfig = new \EasySwoole\CodeGeneration\ModelGeneration\ModelConfig($schemaInfo, $tablePre, "{$path}", $extendClass);
    $modelConfig->setRootPath(EASYSWOOLE_ROOT);//设置项目运行目录,默认为当前执行脚本目录.
    //$modelConfig->setIgnoreString(['list', 'log']);//生成时忽略表名存在的字符,例如user_list将生成=>UserModel

    $modelGeneration = new \EasySwoole\CodeGeneration\ModelGeneration\ModelGeneration($modelConfig);
    $result = $modelGeneration->generate();
    var_dump($result);//生成成功返回生成文件路径,否则返回false
});



//var_dump(\EasySwoole\EasySwoole\Core::getInstance());
go(function(){

    // 生成基础类
    /*
    $generation = new \EasySwoole\CodeGeneration\InitBaseClass\Controller\ControllerGeneration();
    $generation->generate();
    $generation = new \EasySwoole\CodeGeneration\InitBaseClass\UnitTest\UnitTestGeneration();
    $generation->generate();
    $generation = new \EasySwoole\CodeGeneration\InitBaseClass\Model\ModelGeneration();
    $generation->generate();
    //获取Dev.php的MYSQL配置文件
    $mysqlConfig = new \EasySwoole\ORM\Db\Config(\EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL'));

    //var_dump($mysqlConfig);

    //获取数据库连接
    $connection = new \EasySwoole\ORM\Db\Connection($mysqlConfig);

    //指定表名
    $table_pre ="eb_";
    $path ="App\\Model";
    $tableName="store_order";

    $tableObjectGeneration = new \EasySwoole\ORM\Utility\TableObjectGeneration($connection,$tableName);
    //表的结构
    $schemaInfo = $tableObjectGeneration->generationTable();

    $extendClass = \EasySwoole\ORM\AbstractModel::class;

    $modelConfig = new \EasySwoole\CodeGeneration\ModelGeneration\ModelConfig($schemaInfo,$table_pre,"{$path}",$extendClass);
    //设置项目运行目录，默认为当前执行脚本目录
    $modelConfig->setRootPath(EASYSWOOLE_ROOT);

    $modelGeneration = new \EasySwoole\CodeGeneration\ModelGeneration\ModelGeneration($modelConfig);
    $result = $modelGeneration->generate();
    var_dump($result);
    //$modelConfig->setIgnoreString();;

    //$codeGeneration = new \EasySwoole\CodeGeneration\CodeGeneration($tableName,$connection);

    // 生成 model (默认生成模型的路径前缀为 App\Model 目录，例如：如下生成的模型文件为 User模型【\App\Model\User\UserModel.php】 和 基础模型【\App\Model\User\BaseModel.php】)
   /* $codeGeneration->generationModel("\\StoreOrder");
    $tableName="ada_trade";

    $model_ada_trade_codeGeneration = new \EasySwoole\CodeGeneration\CodeGeneration($tableName,$connection);
    $model_ada_trade_codeGeneration->generationModel("\\AdaTrade");
    */

});

\Swoole\Timer::clearAll();

