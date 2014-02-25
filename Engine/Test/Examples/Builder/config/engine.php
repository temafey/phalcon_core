<?php

if (!defined('ROOT')) {
    define('ROOT', realpath(dirname(__DIR__)));
}

return new \Phalcon\Config([
    'database' => [
        'adapter' => 'Mysql',
        'host' => 'localhost',
        'username' => 'root',
        'password' => 'pass',
        'dbname' => 'test_core',
        'charset' => 'utf8'
    ],
    'modules' => [
        'front',
    ],
    'builder' => [
        'modules' => [
            'front' => [
                'modelsDir' => ROOT.'/apps/front/model/',
                'controllersDir' => ROOT.'/apps/front/controller/'
            ]
        ]
    ]
]);