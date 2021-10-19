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
        // 可以自己实现一个标准的 session handler，下面使用组件内置实现的 session handler
        // 基于文件存储，传入 EASYSWOOLE_TEMP_DIR . '/Session' 目录作为 session 数据文件存储位置
        Session::getInstance(new FileSession(EASYSWOOLE_TEMP_DIR . '/Session'));

        Di::getInstance()->set(SysConst::HTTP_GLOBAL_ON_REQUEST, function (Request $request, Response $response) {
            // TODO: 注册 HTTP_GLOBAL_ON_REQUEST 回调，相当于原来的 onRequest 事件

            // 获取客户端 Cookie 中 easy_session 参数
            $sessionId = $request->getCookieParams('easy_session');
            if (!$sessionId) {
                $sessionId = Random::character(32); // 生成 sessionId
                // 设置向客户端响应 Cookie 中 easy_session 参数
                $response->setCookie('easy_session', $sessionId);
            }

            // 存储 sessionId 方便调用，也可以通过其它方式存储
            $request->withAttribute('easy_session', $sessionId);

            Session::getInstance()->create($sessionId); // 创建并返回该 sessionId 的 context
        });

        Di::getInstance()->set(SysConst::HTTP_GLOBAL_AFTER_REQUEST, function (Request $request, Response $response) {
            // TODO: 注册 HTTP_GLOBAL_AFTER_REQUEST 回调，相当于原来的 afterRequest 事件

            // session 数据落地【必不可少这一步】
            Session::getInstance()->close($request->getAttribute('easy_session'));

            // gc 会清除所有 session，切勿操作
            // Session::getInstance()->gc(time());
        });
    }

    /**
     * 主服務器啟動
     * @param EventRegister $register
     */
    public static function mainServerCreate(EventRegister $register)
    {
        //热重启
        $watcher = new FileWatcher();
        $rule = new WatchRule(EASYSWOOLE_ROOT . "/App"); // 设置监控规则和监控目录
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
        //添加定時器
        $register->add(EventRegister::onWorkerStart, function ($server, $workerId) use ($redisConfig, $watcher) {
            if ($workerId == 0) {
                Timer::getInstance()->loop(5 * 1000, function () {
                    //Logger::getInstance()->console(date("Y-m-d H:i:s")." success.",false);
                }, "loglog");

                $queue = new Queue(new RedisQueue($redisConfig));
                $timerID_crontabID = Timer::getInstance()->loop(5* 1000, function () {
                    $ada_usdt_url = "https://www.okex.com/api/v5/market/trades?instId=ADA-USDT&limit=5";
                    //echo $ada_usdt_url . PHP_EOL;
                    //获取最近的 ADA-USDT对的交易信息
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
                                //echo date('Y-m-d H:i:s').";订单号:".$item['tradeId'].";"."数量:".$item['sz'].";方向:".$item['side'].PHP_EOL;
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
                                    //插入数据
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

                //10秒后执行一次
                $timerID_after = Timer::getInstance()->after(10*1000,function(){
                    echo "10 after ,exec only one.".PHP_EOL;
                });

                //var_dump($timerID_after,$timerID_crontabID);
                //Timerlist
                //var_dump(Timer::getInstance()->list());
                //进程内调用协程
                go(function(){
                    //获取数据库的表结构
                    $mysqlConfig = new \EasySwoole\ORM\Db\Config(\EasySwoole\EasySwoole\Config::getInstance()->getConf("MYSQL"));
                    //var_dump($mysqlConfig);
                    $connection = new \EasySwoole\ORM\Db\Connection($mysqlConfig);
                    //var_dump($connection);
                    $tableName = "eb_store_order";

                    //获取表的结构
                    $tableObjectGeneration = new \EasySwoole\ORM\Utility\TableObjectGeneration($connection,$tableName);
                    $schemaInfo = $tableObjectGeneration->generationTable();
                    //var_dump($schemaInfo);
                });


            }

            if($workerId == 1){
                //订单超时
                $result = \Swoole\Coroutine::getOptions();
                var_dump($result);

                //查看定时任务，检测订单


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
                echo "同步任务....".date("Y-m-d H:i:s").PHP_EOL;
            });
            var_dump($taskIDs);

            TaskManager::getInstance()->async(function(){
                echo "async task...".date("Y-m-d H:i:s").PHP_EOL;
            },function ($reply, $taskId, $workerIndex) {
                // $reply 返回的执行结果
                // $taskId 任务id
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
            'enableCoroutine'=>true, //设置自定义进程自动开启协程；

        ]);
        //注册自定义进程
        $processConfig2 = new \EasySwoole\Component\Process\Config([
            'processName'=>'CustomProcess2',
            'processGroup'=>'Custome',
            'arg'=>[
                'arg1'=>'this is arg1!'
            ],
            'enableCoroutine'=>true, //设置自定义进程自动开启协程；

        ]);
        $customeProcess = new \App\Processes\CustomProcess($processConfig);
        //var_dump($customeProcess);
        $customeProcess2 = new \App\Processes\CustomProcess($processConfig2);

        \EasySwoole\Component\Di::getInstance()->set("customSwooleProcess",$customeProcess);
        \EasySwoole\Component\Di::getInstance()->set("customSwooleProcess2",$customeProcess2);

        \EasySwoole\Component\Process\Manager::getInstance()->addProcess($customeProcess);
        \EasySwoole\Component\Process\Manager::getInstance()->addProcess($customeProcess2);

        //注册自定义事件
        \App\Event\CustomEvent::getInstance()->set("customeEvent",function(){
            echo "this is a test event".PHP_EOL;
        });

        ///消息队列
        $redisConfig = new \EasySwoole\Redis\Config\RedisConfig(\EasySwoole\EasySwoole\Config::getInstance()->getConf("REDIS"));
        //配置队列驱动器，这里选择的是redis
        $redis_driver = new \EasySwoole\Queue\Driver\RedisQueue($redisConfig,"main_server_start_queue");
        //自定义消息队列
        MyQUeue::getInstance($redis_driver);
        //注册一个消费队列



    }
}

