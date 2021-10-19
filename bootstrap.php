<?php
//全局bootstrap事件
date_default_timezone_set('Asia/Shanghai');

/**
 * 命令行生成Controller,Model等
 */

\EasySwoole\EasySwoole\Core::getInstance()->initialize();

$mysqlConfig = new \EasySwoole\ORM\Db\Config(\EasySwoole\EasySwoole\Config::getInstance()->getConf("MYSQL"));
//获取连接
$connection = new \EasySwoole\ORM\Db\Connection($mysqlConfig);
//1. 注入到Di 容器中
\EasySwoole\Component\Di::getInstance()->set("CodeGeneration.connection",$connection);
## 2 直接注入 mysql 配置对象
//   $mysqlConfig = new \EasySwoole\ORM\Db\Config(\EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL'));
//   \EasySwoole\Component\Di::getInstance()->set('CodeGeneration.connection', $mysqlConfig);

## 3 直接注入 mysql 配置项
//   \EasySwoole\Component\Di::getInstance()->set('CodeGeneration.connection',\EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL'));

// 注入执行目录项，后面的为默认值，initClass 不能通过注入改变目录
\EasySwoole\Component\Di::getInstance()->set('CodeGeneration.modelBaseNameSpace', "App\\Model");
\EasySwoole\Component\Di::getInstance()->set('CodeGeneration.controllerBaseNameSpace', "App\\HttpController");
\EasySwoole\Component\Di::getInstance()->set('CodeGeneration.unitTestBaseNameSpace', "UnitTest");
\EasySwoole\Component\Di::getInstance()->set('CodeGeneration.rootPath', getcwd());
