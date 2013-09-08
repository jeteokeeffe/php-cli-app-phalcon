<?php

/**
 * Autoload Class files by namespace
 * 
 * @author Jete O'Keeffe
 * @eg 
 	'namespace' => '/path/to/dir'
 */

$autoload = [
	'Utilities\Debug' => $dir . '/library/utilities/debug/',
	'Application' => $dir . '/library/application/',
	'Interfaces' => $dir . '/library/interfaces/',
	'Controllers' => $dir . '/controllers/',
	'Models' => $dir . '/models/',
	'Tasks' => $dir . '/tasks/',
	'Cli' => $dir . '/library/cli/'
];

return $autoload;
