<?php


namespace EasySwoole\EasySwoole;


use App\Models\AdaUsdt;
use App\Utility\MyQUeue;
use EasySwoole\Component\Timer;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;

use EasySwoole\EasySwoole\Task\TaskManager;
use EasySwoole\FileWatcher\FileWatcher;
use EasySwoole\FileWatcher\WatchRule;


use App\Tools\Session;
use EasySwoole\Component\Di;
use EasySwoole\Redis\Config\RedisConfig;
use EasySwoole\Session\FileSession;

use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\Utility\Random;

use EasySwoole\ORM\DbManager;
use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\Db\Config;
use Swoole\Coroutine;
use Swoole\Coroutine\Redis;
use EasySwoole\Queue\Queue;
use EasySwoole\Queue\Job;
use EasySwoole\Queue\Driver\RedisQueue;

use EasySwoole\Mysqli\QueryBuilder;

use EasySwoole\EasySwoole\Config as GlobalConfig;

//curl xiecheng
use function Swoole\Coroutine\run;
use function Swoole\Coroutine\go;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');
        $dev_config = new Config(GlobalConfig::getInstance()->getConf("MYSQL"));
        //Global Config
        //$config_read = new Config(GlobalConfig::getInstance()->getConf("MYSQL_read"));
        //$config_write = new Config(GlobalConfig::getInstance()->getConf("MYSQL_write"));
        //database
        /*$databaseconfig = new Config();
        $databaseconfig->setDatabase("crmeb");
        $databaseconfig->setUser("root");
        $databaseconfig->setPassword("iServer123");
        $databaseconfig->setHost("192.168.21.99");
        $databaseconfig->setPort("3306");
        $databaseconfig->setTimeout(15);
        DbManager::getInstance()->addConnection(new Connection($databaseconfig));*/
        //DbManager::getInstance()->addConnection($config_read, 'read');
        //DbManager::getInstance()->addConnection($config_write, 'write');
        DbManager::getInstance()->addConnection(new Connection($dev_config));

        //DbManager::getInstance()->addConnection(new Connection($databaseconfig), "write");
        // ????????????????????????????????? session handler???????????????????????????????????? session handler
        // ??????????????????????????? EASYSWOOLE_TEMP_DIR . '/Session' ???????????? session ????????????????????????
        Session::getInstance(new FileSession(EASYSWOOLE_TEMP_DIR . '/Session'));

        Di::getInstance()->set(SysConst::HTTP_GLOBAL_ON_REQUEST, function (Request $request, Response $response) {
            // TODO: ?????? HTTP_GLOBAL_ON_REQUEST ??????????????????????????? onRequest ??????

            // ??????????????? Cookie ??? easy_session ??????
            $sessionId = $request->getCookieParams('easy_session');
            if (!$sessionId) {
                $sessionId = Random::character(32); // ?????? sessionId
                // ???????????????????????? Cookie ??? easy_session ??????
                $response->setCookie('easy_session', $sessionId);
            }

            // ?????? sessionId ????????????????????????????????????????????????
            $request->withAttribute('easy_session', $sessionId);

            Session::getInstance()->create($sessionId); // ?????????????????? sessionId ??? context
        });

        Di::getInstance()->set(SysConst::HTTP_GLOBAL_AFTER_REQUEST, function (Request $request, Response $response) {
            // TODO: ?????? HTTP_GLOBAL_AFTER_REQUEST ??????????????????????????? afterRequest ??????

            // session ???????????????????????????????????????
            Session::getInstance()->close($request->getAttribute('easy_session'));

            // gc ??????????????? session???????????????
            // Session::getInstance()->gc(time());
        });
    }

    /**
     * ??????????????????
     * @param EventRegister $register
     */
    public static function mainServerCreate(EventRegister $register)
    {
        //?????????
        $watcher = new FileWatcher();
        $rule = new WatchRule(EASYSWOOLE_ROOT . "/App"); // ?????????????????????????????????
        $redisConfig = new RedisConfig([
            'host' => '127.0.0.1',
            'port' => '6379',
            'serialize' => RedisConfig::SERIALIZE_NONE,
            "db" => 0,
        ]);
        $watcher->addRule($rule);
        $watcher->setOnChange(function () {
            Logger::getInstance()->info('file change ,reload success.');
            ServerManager::getInstance()->getSwooleServer()->reload();
        });
        $watcher->attachServer(ServerManager::getInstance()->getSwooleServer());
        //???????????????
        $register->add(EventRegister::onWorkerStart, function ($server, $workerId) use ($redisConfig, $watcher) {
            if ($workerId == 0) {
                Timer::getInstance()->loop(5 * 1000, function () {
                    //Logger::getInstance()->console(date("Y-m-d H:i:s")." success.",false);
                }, "loglog");

                $queue = new Queue(new RedisQueue($redisConfig));
                $timerID_crontabID = Timer::getInstance()->loop(5* 1000, function () {
                    $ada_usdt_url = "https://www.okex.com/api/v5/market/trades?instId=ADA-USDT&limit=5";
                    //echo $ada_usdt_url . PHP_EOL;
                    //??????????????? ADA-USDT??????????????????
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $ada_usdt_url);
                    curl_setopt($ch, CURLOPT_HEADER, false);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $result = curl_exec($ch);
                    curl_close($ch);
                    $result_array = json_decode($result, true);
                    ///var_dump($result_array);

                    if ($result_array) {
                        foreach ($result_array['data'] as $item) {
                            try {
                                DbManager::getInstance()->startTransaction();
                                //echo date('Y-m-d H:i:s').";?????????:".$item['tradeId'].";"."??????:".$item['sz'].";??????:".$item['side'].PHP_EOL;
                                /*   $sql = "replace into ada_trade(tradeID) values(".$item['tradeId'].");".PHP_EOL;
                                   echo $sql;
                                   $queryBuilder = new QueryBuilder();
                                   $res= $queryBuilder->raw("replace into ada_trade(tradeID)values(?)",$item['tradeId']);
                                   //var_dump($res);
                                   DBManager::getInstance()->commit();
                                   return true;*/
                                $ada_trade = AdaUsdt::create()->get(['tradeID' => $item['tradeId']]);
                                //var_dump($ada_trade);

                                //var_dump($ada_trade);
                                /*   $ada_model = AdaUsdt::create();
                                   $result = $ada_model->get([
                                       'tradeID'=>$item['tradeId']
                                   ]);*/
                                //var_dump($ada_trade);

                                if ($ada_trade == NULL) {
                                   // var_dump($item);
                                    $ada_model = AdaUsdt::create([
                                        'tradeID'=>$item['tradeId'],
                                        'trade'=>$item['side'],
                                        'nums'=>$item['sz'],
                                        'addtime'=>date("Y-m-d H:i:s"),
                                        'trade_price' => $item['px'],
                                        'ts'=>$item['ts']
                                    ]);
                                    //????????????
                                    $ada_model = AdaUsdt::create();
                                    $ada_model->tradeID=$item['tradeId'];
                                    $ada_model->trade=$item['side'];
                                    $ada_model->nums=$item['sz'];
                                    $ada_model->addtime=date("Y-m-d H:i:s");
                                    $ada_model->trade_price=$item['px'];
                                    $ada_model->ts=$item['ts'];
                                    $ada_model->save();
                                    //$save_result = $ada_model->save();
                                    //var_dump($save_result);
                                    /*$ada_model->insert(array(
                                        "tradeID"=>$item['tradeId'],
                                        "trade"=>$item['side'],
                                        "nums"=>$item['sz'],
                                        "addtime"=>date("Y-m-d H:i:s"),
                                        "trade_price"=>$item['px'],
                                        "ts"=>$item['ts']
                                        ));*/
                                }
                                DbManager::getInstance()->commit();
                            } catch (Exception $e) {

                                var_dump($e->getMessage());
                                DbManager::getInstance()->rollback();
                            }

                        }

                    } else {
                        echo date("Y-m-d H:i:s") . "; not found data." . PHP_EOL;
                    }
                }, "okexapi");

                //10??????????????????
                $timerID_after = Timer::getInstance()->after(10*1000,function(){
                    echo "10 after ,exec only one.".PHP_EOL;
                });

                //var_dump($timerID_after,$timerID_crontabID);
                //Timerlist
                //var_dump(Timer::getInstance()->list());
                //?????????????????????
                go(function(){
                    //???????????????????????????
                    $mysqlConfig = new \EasySwoole\ORM\Db\Config(\EasySwoole\EasySwoole\Config::getInstance()->getConf("MYSQL"));
                    //var_dump($mysqlConfig);
                    $connection = new \EasySwoole\ORM\Db\Connection($mysqlConfig);
                    //var_dump($connection);
                    $tableName = "eb_store_order";

                    //??????????????????
                    $tableObjectGeneration = new \EasySwoole\ORM\Utility\TableObjectGeneration($connection,$tableName);
                    $schemaInfo = $tableObjectGeneration->generationTable();
                    //var_dump($schemaInfo);
                });


            }

            if($workerId == 1){
                //????????????
                $result = \Swoole\Coroutine::getOptions();
                var_dump($result);

                //?????????????????????????????????


            }
            if ($workerId == 2) {
                Timer::getInstance()->loop(2 * 1000, function () {
                    $redis = new \EasySwoole\Redis\Redis(
                        new RedisConfig([
                            'host' => '127.0.0.1',
                            'port' => '6379',
                            'serialize' => RedisConfig::SERIALIZE_NONE
                        ])
                    );
                    $redis->connect();
                    $key = "test";
                    $data = $redis->incr($key);
                    //var_dump($data);

                });

            }
            if ($workerId == 3) {
                $redis = new \EasySwoole\Redis\Redis(
                    new RedisConfig([
                        'host' => '127.0.0.1',
                        'port' => '6379',
                        'serialize' => RedisConfig::SERIALIZE_NONE
                    ])
                );
                $redis->connect();
                $key = "hash_list";
                for ($i = 0; $i < 1000; $i++) {
                    $redis->hSet($key, "aa" . $i, "33333" . ($i + 3));
                }
                $key = "list_list";
                for ($i = 10000; $i < 99999; $i++) {
                    $redis->lPush($key, $i);
                }
                /*
                Timer::getInstance()->loop(2*1000,function() use($workerId){
                    $redis = new \EasySwoole\Redis\Redis(
                        new RedisConfig([
                            'host'=>'127.0.0.1',
                            'port'=>'6379',
                            'serialize'      =>RedisConfig::SERIALIZE_NONE
                        ])
                    );
                    $redis->connect();
                    $key = "hash_list";
                    for($i=0; $i<2;$i++){
                        $redis->hSet($key,$i.$i.$i.$i,($i+1).$i.$i);
                    }

                });
                */
            }
            if ($workerId == 1) {
                go(function () {
                    $redis = new \EasySwoole\Redis\Redis(
                        new RedisConfig([
                            'host' => '127.0.0.1',
                            'port' => '6379',
                            'serialize' => RedisConfig::SERIALIZE_NONE
                        ])
                    );

                    $data = $redis->connect();
                    if ($data === true) {
                        echo date("Y-m-d H:i:s") . " redis init success;" . PHP_EOL;
                    }
                    /* //var_dump($data);
                     $key = "order_key";
                     $redis->set($key,"test");
                     $data = $redis->exists($key);
                     var_dump($data);*/

                });
            }
            if ($workerId == 4) {
                $ada_usdt_url = "https://www.okex.com/api/v5/market/trades?instId=ADA-USDT";
            }
            if ($workerId == 5) {
                //echo "test" . PHP_EOL;
            }
            /*
            $taskIDs=TaskManager::getInstance()->sync(function($taskID,$workerId){
                var_dump($taskID,$workerId);
                return "success";
                echo "????????????....".date("Y-m-d H:i:s").PHP_EOL;
            });
            var_dump($taskIDs);

            TaskManager::getInstance()->async(function(){
                echo "async task...".date("Y-m-d H:i:s").PHP_EOL;
            },function ($reply, $taskId, $workerIndex) {
                // $reply ?????????????????????
                // $taskId ??????id
                echo 'async success';
            });
            */
        });
        //
        $processConfig = new \EasySwoole\Component\Process\Config([
            'processName'=>'CustomProcess',
            'processGroup'=>'Custome',
            'arg'=>[
                'arg1'=>'this is arg1!'
            ],
            'enableCoroutine'=>true, //??????????????????????????????????????????

        ]);
        //?????????????????????
        $processConfig2 = new \EasySwoole\Component\Process\Config([
            'processName'=>'CustomProcess2',
            'processGroup'=>'Custome',
            'arg'=>[
                'arg1'=>'this is arg1!'
            ],
            'enableCoroutine'=>true, //??????????????????????????????????????????

        ]);
        $customeProcess = new \App\Processes\CustomProcess($processConfig);
        //var_dump($customeProcess);
        $customeProcess2 = new \App\Processes\CustomProcess($processConfig2);

        \EasySwoole\Component\Di::getInstance()->set("customSwooleProcess",$customeProcess);
        \EasySwoole\Component\Di::getInstance()->set("customSwooleProcess2",$customeProcess2);

        \EasySwoole\Component\Process\Manager::getInstance()->addProcess($customeProcess);
        \EasySwoole\Component\Process\Manager::getInstance()->addProcess($customeProcess2);

        //?????????????????????
        \App\Event\CustomEvent::getInstance()->set("customeEvent",function(){
            echo "this is a test event".PHP_EOL;
        });

        ///????????????
        $redisConfig = new \EasySwoole\Redis\Config\RedisConfig(\EasySwoole\EasySwoole\Config::getInstance()->getConf("REDIS"));
        //??????????????????????????????????????????redis
        $redis_driver = new \EasySwoole\Queue\Driver\RedisQueue($redisConfig,"main_server_start_queue");
        //?????????????????????
        MyQUeue::getInstance($redis_driver);
        //????????????????????????



    }
}

