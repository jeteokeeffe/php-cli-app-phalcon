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
use \Interfaces\IEvent as IEvent;

class Debug extends \Phalcon\Events\Manager implements IEvent {

	/**
	 * Constructor
 	 */
	public function __construct($enable = TRUE) {
		if ($enable === TRUE) { 
			$this->handleEvent();
		}
	}


	/**
	 * Setup an event to trigger after running task
 	 */
	public function handleEvent() {
		$this->attach('console:afterHandleTask', function ($event, $console) {
			$this->display($console);
		});
	}


	/**
	 *
	 */
	public function display($console) {

		$dispatcher = $console->getDI()->getShared('dispatcher');

		$taskName = $dispatcher->getTaskName();
		$actionName = $dispatcher->getActionName();

		if (!class_exists('\\Cli\Output')) {
			fwrite(STDERR, "Unable to find Output class" . PHP_EOL);
			return;
		}

		// Get Memory Usage
		$curMem = memory_get_usage(FALSE);
		$curRealMem = memory_get_usage(TRUE);
		$peakMem = memory_get_peak_usage(FALSE);
		$peakRealMem = memory_get_peak_usage(TRUE);
		// Get Time
		$totalTime = microtime(TRUE) - $_SERVER['REQUEST_TIME'];
		$startTime = date('m/d/y h:i:s', $_SERVER['REQUEST_TIME']);

		Output::stdout("");
		Output::stdout(Output::COLOR_BLUE . "--------------DEBUG ENABLED---------------------" . Output::COLOR_NONE);
		Output::stdout(Output::COLOR_BLUE . "+++Overview+++" . Output::COLOR_NONE);
		Output::stdout("task: $taskName");
		Output::stdout("action: $actionName");
		Output::stdout("total time: $totalTime start time: $startTime end time: " . date('m/d/y h:i:s'));
		if ($console->isRecording()) {
			Output::stdout("task id: " . $console->getTaskId());
		}
		Output::stdout("hostname: " . php_uname('n'));
		Output::stdout("pid: " . getmypid());

		if ($console->isSingleInstance()) {
			Output::stdout("pid file: " . \Cli\Pid::singleton('')->getFileName() );
		}

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

		// Print out Queries 
		if ($console->getDI()->has('profiler')) {
			Output::stdout(Output::COLOR_BLUE . "+++Queries+++" . Output::COLOR_NONE);
			$profiles = $console->getDI()->getShared('profiler')->getProfiles();
			if (!empty($profiles)) {
				foreach ($profiles as $profile) {
					Output::stdout($profile->getSQLStatement());
					Output::stdout("time: " . $profile->getTotalElapsedSeconds());
					Output::stdout("");
				}
				Output::stdout("");
			}
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

		// Print out all included php files
		$files = get_required_files();
		Output::stdout(Output::COLOR_BLUE . "+++Included Files+++" . Output::COLOR_NONE);
		foreach($files as $file) {
			Output::stdout($file);	
		}
		Output::stdout("");
	}
}
