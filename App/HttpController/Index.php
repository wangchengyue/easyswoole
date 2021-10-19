<?php


namespace App\HttpController;


use EasySwoole\Http\AbstractInterface\Controller;

use EasySwoole\Spl\SplBean;
use Simps\MQTT\Config\ClientConfig as MqttClientConfig;
use Simps\MQTT\Client as MqttClient;
use EasySwoole\EasySwoole\Task\TaskManager;


use EasySwoole\Redis\Config\RedisConfig;
use EasySwoole\Queue\Queue;
use EasySwoole\Queue\Job;
use EasySwoole\Queue\Driver\RedisQueue;
use EasySwoole\EasySwoole\Config;

class Index extends Controller
{
    const SIMPS_MQTT_LOCAL_HOST = '127.0.0.1';
    const SIMPS_MQTT_REMOTE_HOST = "broker-cn.emqx.io";
    const SIMPS_MQTT_PORT = 1883;
    const SWOOLE_MQTT_CONFIG = [
        'open_mqtt_protocol' => true,
        'package_max_length' => 2 * 1024 * 1024,
        'connect_timeout' => 5.0,
        'write_timeout' => 5.0,
        'read_timeout' => 5.0,
    ];

    public static function getTestConnectConfig()
    {
        $config = new MqttClientConfig();
        return $config->setUserName('')
            ->setPassword('')
            ->setClientId(MqttClient::genClientID())
            ->setKeepAlive(10)
            ->setDelay(3000)
            ->setMaxAttempts(5)
            ->setSwooleConfig(self::SWOOLE_MQTT_CONFIG);
    }

    /**
     * mqtt訂閱
     */
    public function subscribe()
    {

        go(function () {
            $client = new MqttClient(self::SIMPS_MQTT_LOCAL_HOST, self::SIMPS_MQTT_PORT, self::getTestConnectConfig());
            var_dump("test");
            $client->connect();
            while (true) {
                $response = $client->publish('simps-mqtt/user001/update', '{"time":' . time() . '}', 1);
                var_dump($response);
                Coroutine::sleep(3);
            }
        });

        /*        \Swoole\Coroutine::create(function () {
                    $config = [
                        'host' => 'broker-cn.emqx.io',
                        'port' => 1883,
                        'time_out' => 5,
                        'user_name' => '',
                        'password' => '',
                        'client_id' => MqttClient::genClientID(),
                        'keep_alive' => 10,
                        'properties' => [
                            'session_expiry_interval' => 60,
                            'receive_maximum' => 200,
                            'topic_alias_maximum' => 200,
                        ],
                        'protocol_level' => 5,
                    ];

                    $configObject = new MqttClientConfig($config);
                    /// $client = new Client($config,['open_mqtt_protocol' => true, 'package_max_length' => 2 * 1024 * 1024]);
                    //客户端
                    $client = new MqttClient("127.0.0.1", "1883", $configObject);

                    $will = [
                        'topic' => 'test', // 主题
                        'qos' => 1, // QoS等级
                        'retain' => 0, // retain标记
                        'message' => '', // 遗嘱消息内容
                        'properties' => [], // MQTT5 中需要，可选
                    ];
                    $result = $client->connect(true,$will);
                    var_dump($result);
                });*/
        //$this->response()->withHeader('Content-Type', 'application/json');
        $this->response()->write("success");
    }

    /**
     * mqtt發布
     */
    public function publish()
    {

    }

    public function index()
    {
        $file = EASYSWOOLE_ROOT . '/vendor/easyswoole/easyswoole/src/Resource/Http/welcome.html';
        if (!is_file($file)) {
            $file = EASYSWOOLE_ROOT . '/src/Resource/Http/welcome.html';
        }
        $this->response()->write(file_get_contents($file));
    }

    /**
     * 会触发定时任务
     */
    public function task()
    {
        $taskid1 = TaskManager::getInstance()->async(function () {
            echo date('Y-m-d H:i:s') . "; success...";
        }, function ($reply, $taskId, $workerIndex) {
            // $reply 返回的执行结果
            // $taskId 任务id
            return $taskId;
        });
        $taskid2 = TaskManager::getInstance()->sync(function () {
            return "success";
        }, 5);
        $this->response()->write("aaa" . $taskid1 . ";" . $taskid2);
    }

    function test()
    {
        $this->response()->write('this is test');
    }

    /**
     * 向自定义进程中传递消息
     */
    function process()
    {
        $customeProcess = \Easyswoole\Component\Di::getInstance()->get("customSwooleProcess");
        //向自定义进程中传递信息，触发自定义进程的onPipeReadable
        $customeProcess->write("this is a test");
        //触发自定义事件
        \App\Event\CustomEvent::getInstance()->hook("customeEvent");
        $this->response()->write("this is a test ");

    }

    protected function actionNotFound(?string $action)
    {
        $this->response()->withStatus(404);
        $file = EASYSWOOLE_ROOT . '/vendor/easyswoole/easyswoole/src/Resource/Http/404.html';
        if (!is_file($file)) {
            $file = EASYSWOOLE_ROOT . '/src/Resource/Http/404.html';
        }
        $this->response()->write(file_get_contents($file));
    }

    /**
     * 消息队列
     */
    public function queue()
    {
        $redisConfig = Config::getInstance()->getConf("REDIS");
        //var_dump($redisConfig);
        $queue = new Queue(new RedisQueue(new RedisConfig($redisConfig),"redis_consmer_list"));
        //var_dump($queue);
        //普通生产任务
        $job = new Job();
        //设置任务数据
        $job->setJobData("this is my job data time time " . date('Ymd h:i:s'));
        $job->setRetryTimes(3);
        $job->setWaitConfirmTime(5);

        //生产普通任务
        $queue->producer()->push($job);

         $queue->consumer()->pop();
        //var_dump($job);

        /*$queue->consumer()->listen(function(Job $job){
            var_dump($job);
        });*/


        $this->response()->write("生产普通任务");

    }

    public function popdata()
    {
       /* $redisConfig = Config::getInstance()->getConf("REDIS");
        $queue = new Queue(new RedisQueue(new RedisConfig($redisConfig)));
        $job = $queue->consumer()->pop();
        var_dump($job);
        // 或者是自定义进程中消费任务(具体使用请看下文自定义进程消费任务完整使用示例)
        $queue->consumer()->listen(function (Job $job) {
            var_dump($job);
        });

        $this->response()->write("消费数据");*/

    }
}



