<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

define('ROOT_PATH', __DIR__ . '/../../');

/**
 * Use the application autoloader to autoload the required
 * bootstrap and test helper classes
 */
$loader = new \Phalcon\Loader();
$loader->registerNamespaces([
    'Cli\Test' => ROOT_PATH . '/test/library/cli/',
    'Cli' => ROOT_PATH . '/app/library/cli/'
])->register();