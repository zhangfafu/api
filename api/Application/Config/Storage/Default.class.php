<?php

return array(
    'default' => array(
        'dsn' => 'mysql:host=test-db;dbname=test;',
        'user' => 'test',
        'psw' => '123456',
        'charset' => 'utf8',
        'engine' => 'pdodb',
    ),
    'redisdb' => array(
        'host' => 'redis-test',
        'port' => 6379,
        'engine' => '\Xes\Lib\Redisdb',
    )
);
