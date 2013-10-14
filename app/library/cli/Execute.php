<?php

/**
 * command executer
 *
 * Execute a command thru the shell
 *
 * @author Jete O'Keeffe
 * @version 1.0
 * @package cli
 */

namespace Cli;

class Execute {

	/**
	 * single instance of class (needed for singleton)
	 * @var object 
	 */
	protected static $_instance; 

	/**
	 * array of commands
	 * @var array 
	 */
	protected $_command;

	/**
	 * constructor to initialize class
	 */
	private function __construct() {
		$this->_command = array();
	}

	/**
	 * Get single instance of class
	 * 
	 * @return instance of this class
	 */
	public static function singleton() {
		if (empty(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * execute a command
	 *
	 * @param string $cmd 
	 * @param string $file
	 * @param int $line
	 * @param string $stdout string
	 * @param string $return exit code of command
	 *
	 * @return TRUE|int 
	 */
	public function execute($cmd, $file, $line, &$stdout = NULL, &$stderr = NULL) {
        // Create temporary files to write output/stderr (dont worry about stdin)
        $outFile = tempnam(".", "cli");
        $errFile = tempnam(".", "cli");

        // Map Files to Process's output, input, error to temporary files
        $descriptor = array(0 => array("pipe", "r"),
            1 => array("file", $outFile, "w"),
            2 => array("file", $errFile, "w")
        );

		$start = microtime(TRUE);

        // Start process
        $proc = proc_open($cmd, $descriptor, $pipes);
        if (!is_resource($proc)) {
            $result =  255;
        } else {
            fclose($pipes[0]);
            $return = proc_close($proc);
        }
		$end = microtime(TRUE);

        // Get Output
        $stdout = implode(PHP_EOL, file($outFile));
        $stderr = implode(PHP_EOL, file($errFile));

        // Remove temp files
        unlink($outFile);
        unlink($errFile);
        
		$command = new \Cli\Command;
		$command->command = $cmd;
		$command->file = $file;
		$command->line = $line;
		$command->result_code = $return;
		$command->success = $return == 0 ? TRUE : FALSE;
		$command->stdout = $stdout;
        $command->stderr = $stderr;
		$command->time = ($end - $start);

		$this->_command[] = $command;

		return $return == 0 ? TRUE : $return;
	}


	/**
	 * Get all commands executed
	 * 
	 * @return array of executed commands
	 */
	public function getCommands() {
		return $this->_command;
	}


	/**
	 * Output the object
	 */
	public function __toString() {
		if (PHP_SAPI == 'cli')
			return print_r($this->_command, TRUE);
		else
			return "<pre>" . print_r($this->_command, TRUE) . "</pre>";
	}
}
