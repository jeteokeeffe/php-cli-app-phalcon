<?php

/**
 * Handling of the ProcessID(Pid) file's creation, removal and existance
 *
 * @package Cli
 * @author Jete O'Keeffe
 */

namespace Cli;

class Pid {

	/**
	 *
	 */
	protected $_isCreated;

	/**
	 * 
	 */
	protected $_isRemoved;

	/**
	 *
	 */
	protected static $_instance;

	/**
	 * File pointer
	 */
	protected $_file;

	/**
	 *
	 */
	protected function __construct($file) {
		$this->_pidFile = $file;
		$this->_isCreated = FALSE;
		$this->_file = NULL;
	}

	/**
	 * Get instance
	 *
	 * @param string		file name of the pid file
	 * @param string		directory of the pid file
	 */
	public static function singleton($file, $dir = '/tmp') {
		if (empty(self::$_instance)) {
			self::$_instance = new Process($dir . '/' . $file);	
		}
		return self::$_instance;
	}


	/**
	 * Remove the ProcessID file
	 */
	public function remove() {
		if ($this->_isCreated) {
			// close handle to file and remove it
			fclose($this->_file);
			if ($result = unlink($this->_pidFile)) {
				return $this->_isRemoved = TRUE;
			} else {
				return FALSE;
			}
		}
	}


	/**
	 * Create the ProcessID file
	 */
	public function create() {
		if ($this->exists()) {
			throw new Exception('Pid File exists/Instance of script is already running');
		}

		if (is_writable($this->_pidFile)) {
			throw new Exception('Unable to write to this file');
		}

		$this->_file = fopen($this->_pidFile, 'r+');
		if (!flock($this->_file, LOCK_EX | LOCK_NB)) {
			fclose($this->_file);
			return FALSE;
		}

		$this->_isCreated = TRUE;
		return TRUE;
	}

	/**
	 * Check if the PID file exists
	 */
	public function exists() {
		return file_exists($this->_pidFile);
	}

	/**
	 * Check if Pid has been created
	 */
	public function created() {
		return $this->_isCreated;
	}

	/**
	 * Check if Pid file has been deleted
	 */
	public function removed() {
		return $this->_isRemoved();
	}
}
