<?php

/**
 * Autoload Class files by PHP namespacing support
 * 
 * @author Jete O'Keeffe
 * @eg 
 	$autoload = [
 		'namespace' => '/path/to/dir',
		'namespace' => '/path/to/dir'
	];
	return $autoload;
 *
 * @note $dir is the app directory (/path/to/php-cli-app-phalcon/app)
 */

$autoload = [
	'Utilities\Debug' => $dir . '/library/utilities/debug/',
	'Application' => $dir . '/library/application/',
	'Interfaces' => $dir . '/library/interfaces/',
	'Controllers' => $dir . '/controllers/',
	'Models' => $dir . '/models/',
	'Tasks' => $dir . '/tasks/',
	'Cli' => $dir . '/library/cli/',
	'Events\Cli' => $dir . '/library/events/cli/',
];

return $autoload;
