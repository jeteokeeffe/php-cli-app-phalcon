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

namespace cli;

class Execute {

	/**
	 * single instance of class (needed for singleton)
	 * @var mixed 
	 */
	protected static $_instance; 

	/**
	 * array of commands
	 * @var array 
	 */
	protected $_command;

	/**
	 * turns standard error mode on or off
	 * @var bool 
	 */
	protected $_stderrMode;

	/**
	 * constructor to initialize class
	 */
	private function __construct() {
		$this->_command = array();
		$this->_stderrMode = TRUE;
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
	 * will add an extra flag redirect error output to standard output
	 *
	 * @param bool $add	TRUE or FALSE
	 */
	public function addStderr($add = FALSE) {
		$this->_stderrMode = $add;
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
	public function execute($cmd, $file, $line, &$stdout = NULL, &$return = NULL) {
		if ($this->_stderrMode === TRUE) {
			$cmd .= " 2>&1";
		}

		$start = microtime(TRUE);
		exec($cmd, $stdout, $return);
		$end = microtime(TRUE);

		if (is_array($stdout)) {
			$stdout = implode(PHP_EOL, $stdout);
		}
		
		$command = new Command;
		$command->command = $cmd;
		$command->file = $file;
		$command->line = $line;
		$command->result_code = $return;
		$command->success = $return == 0 ? TRUE : FALSE;
		$command->output = $stdout;
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
