<?php

use EasySwoole\Log\LoggerInterface;

return [
    'SERVER_NAME' => "OrderServer",
    'MAIN_SERVER' => [
        'LISTEN_ADDRESS' => '0.0.0.0',
        'PORT' => 9501,
        'SERVER_TYPE' => EASYSWOOLE_WEB_SERVER, //可选为 EASYSWOOLE_SERVER  EASYSWOOLE_WEB_SERVER EASYSWOOLE_WEB_SOCKET_SERVER
        'SOCK_TYPE' => SWOOLE_TCP,
        'RUN_MODEL' => SWOOLE_PROCESS,
        'SETTING' => [
            'worker_num' => 8,
            'reload_async' => true,
            'max_wait_time' => 3
        ],
        'TASK' => [
            'workerNum' => 10,
            'maxRunningNum' => 128,
            'timeout' => 15
        ],
        "open_mqtt_protocol"=>true,
    ],
    "LOG" => [
        'dir' => null,
        'level' => LoggerInterface::LOG_LEVEL_DEBUG,
        'handler' => null,
        'logConsole' => true,
        'displayConsole' => true,
        'ignoreCategory' => []
    ],
    'TEMP_DIR' => null,
    'LOG_DIR'  => null,
    'MYSQL' => [
        'host'          => "192.168.21.99",
        'port'          => 3306,
        'user'          => 'root',
        'timeout'       => 5,
        'charset'       => 'utf8mb4',
        'password'      => 'iServer123',
        'database'      => 'crmeb',
        'maxObjectNum'  => 20,
        'minObjectNum'  => 5,
        'getObjectTimeout'  => 3.0,
    ],
    'MYSQL_read' => [
        'host'          => '127.0.0.1',
        'port'          => 3306,
        'user'          => 'easyswoole',
        'timeout'       => 5,
        'charset'       => 'utf8mb4',
        'password'      => 'easyswoole100%',
        'database'      => 'easyswoole',
        'maxObjectNum'  => 20,
        'minObjectNum'  => 5,
        'getObjectTimeout'  => 3.0,
    ],
    'MYSQL_write' => [
        'host'          => '127.0.0.1',
        'port'          => 3306,
        'user'          => 'easyswoole',
        'timeout'       => 5,
        'charset'       => 'utf8mb4',
        'password'      => 'easyswoole100%',
        'database'      => 'easyswoole',
        'maxObjectNum'  => 20,
        'minObjectNum'  => 5,
        'getObjectTimeout'  => 3.0,
    ],
    'REDIS'=>[
        'host'      => '127.0.0.1',
        'port'      => '6379',
        'serialize' => \EasySwoole\Redis\Config\RedisConfig::SERIALIZE_NONE
    ]
];
