<?php


namespace App\HttpController;

use EasySwoole\Http\AbstractInterface\Controller;

use EasySwoole\Mysqli\Client as SMysqliClient;

use EasySwoole\Mysqli\QueryBuilder;

class MysqlTest extends Controller{



    public function showdatabase(){
        $config  = new \EasySwoole\Mysqli\Config(

            [
                'host'=>'localhost',
                'port'=>3306,
                'user'=>'root',
                'password'=>'iServer123',
                'database'=>'midway',
                'timeout'=>5,
                'charset'=>'utf8',
            ]
        );
        $mysqlClient = new SMysqliClient($config);

        go(function () use ($mysqlClient){
                //SQl
                $mysqlClient->queryBuilder()->get("demo_app_goods");
                var_dump($mysqlClient->execBuilder());
        });

        $this->response()->withHeader('Content-Type', 'application/json');

        $this->response()->write("aaa");

    }
    public function test(){
        $this->response()->withHeader('Content-Type', 'application/json');
        $this->response()->write("abced");
    }

    public function queryBuilder(){
        $builder = new QueryBuilder();
        $result = $builder->get("base_sys_user");
        var_dump($result->toArray());
        echo $builder->getLastQuery();

    }

    /**
     * 事务
     */
    public function Transaction(){

    }
}
