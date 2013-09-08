<?php

/**
 * a cli launcher
 *
 * a cli script that launches phalcon tasks
 *
 * @package cli
 * @author Jete O'Keeffe
 * @version 1.0
 * @copyright never
 *
 * @example php cli.php [task] [action] [param1 [param2 ...]]
 * @example php cli.php Example index
 * @example php cli.php Example index --debug --single --no-record
 *
 * @notes Make sure to Autoload tasks directory
 */


// Setup configuration files
$dir = dirname(__DIR__);
$appDir = $dir . '/app';

// Necessary requires to get things going
require $appDir . '/library/utilities/debug/PhpError.php';
require $appDir . '/library/interfaces/IRun.php';
require $appDir . '/library/application/Cli.php';

// Capture runtime errors
register_shutdown_function(['Utilities\Debug\PhpError','runtimeShutdown']);

// Necessary paths to autoload & config settings
$configPath = $appDir . '/config/';
$config = $configPath . 'config.php';
$autoLoad = $configPath . 'autoload.php';

try {

	$mode = 0;
	$app = new Application\Cli($mode);

	// Record any php warnings/errors
	set_error_handler(['Utilities\Debug\PhpError','errorHandler']);

	$app->setAutoload($autoLoad, $appDir);
	$app->setConfig($config);

	$app->setArgs($argv, $argc);

	// Boom, Run
	$app->run();

} catch(Exception $e) {
	echo $e;
	exit(255);
}

?>
