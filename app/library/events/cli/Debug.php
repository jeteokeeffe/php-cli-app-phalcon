<?php

/**
 * Event that displays debug output at the end of the application
 * 
 * @package Cli
 * @subpackage Events
 * @author Jete O'Keeffe
 * @version 1.0
 */

namespace Events\Cli;

use \Cli\Output as Output;
use \Cli\Execute as Execute;

class Debug extends \Phalcon\Events\Manager {

	/**
	 * Constructor
 	 */
	public function __construct() {
		$this->createEvent();
	}


	/**
 	 */
	public function createEvent() {
		$this->attach('console:afterHandleTask', function ($event, $console) {
			$dispatcher = $console->getDI()->getShared('dispatcher');

			$taskName = $dispatcher->getTaskName();
			$actionName = $dispatcher->getActionName();

			if (!class_exists('\\cli\Output')) {
				fwrite(STDERR, "Unable to find Output class" . PHP_EOL);
				return;
			}

			// Get Memory Usage
			$curMem = memory_get_usage(FALSE);
			$curRealMem = memory_get_usage(TRUE);
			$peakMem = memory_get_peak_usage(FALSE);
			$peakRealMem = memory_get_peak_usage(TRUE);

			Output::stdout("");
			Output::stdout("--------------DEBUG ENABLED---------------------");
			Output::stdout("total time: " . (microtime(TRUE) - $_SERVER['REQUEST_TIME'] ));
			Output::stdout("hostname: " . php_uname('n'));
			Output::stdout("pid: " . getmypid());

			if ($console->isSingleInstance()) {
				Output::stdout("pid file: " . $console->getPidFile() );
			}

			Output::stdout("");
			Output::stdout("task: $taskName");
			Output::stdout("action: $actionName");
			Output::stdout("");
			Output::stdout("current memory: $curMem bytes " . round($curMem/1024, 2) . " kbytes" );
			Output::stdout("current real memory: $curRealMem bytes " . round($curRealMem/1024, 2) . " kbytes");
			Output::stdout("peak memory: $peakMem bytes " . round($peakMem/1024, 2) . " kbytes");
			Output::stdout("peak real memory: $peakRealMem bytes " . round($peakRealMem/1024, 2) . " kbytes");
			Output::stdout("");

			// Print out Commands
			$commands = Execute::singleton()->getCommands();
			if (!empty($commands)) {
				Output::stdout(Output::COLOR_BLUE . "+++Cli Commands+++" . Output::COLOR_NONE);
				foreach($commands as $command) {
					Output::stdout($command->command);
					Output::stdout($command->file . "\t" . $command->line . "\t" . ($command->success ?
						Output::COLOR_GREEN . "Success" . Output::COLOR_NONE :
							Output::COLOR_RED . "Failed" . Output::COLOR_NONE));
					Output::stdout("");
				}
				Output::stdout("");
			}

			// Print out Exceptions
			/*$exceptions = Logger::getInstance()->getAll();
			if (!empty($exceptions)) {
				Output::stdout(Output::COLOR_BLUE . "+++Exceptions+++" . Output::COLOR_NONE);
				foreach($exceptions as $except) {
					Output::stdout($except->getMessage());
					Output::stdout($except->getCode() . "\t" . $except->getFile() . "\t" . $except->getLine());
					Output::stdout("");
				}
				Output::stdout("");
			}*/
		
		});
	}
}
