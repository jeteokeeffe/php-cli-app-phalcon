<?php

/**
 * Settings to be stored in dependency injector
 */

$settings = [
    'database' => [
        'adapter'  => 'Mysql',
        'host'     => 'localhost',
        'username' => 'test',
        'password' => 'test',
        'name'     => 'cli',
        'port'     => 3306
    ],
];


return $settings;
