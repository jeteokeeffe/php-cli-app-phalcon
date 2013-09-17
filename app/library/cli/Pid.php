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
	protected $_fp;

	/**
	 *
	 */
	protected function __construct($file) {
		$this->_pidFile = $file;
		$this->_isCreated = FALSE;
		$this->_isRemoved = FALSE;
		$this->_fp = NULL;
	}

	/**
	 * Get instance
	 *
	 * @param string		file name of the pid file
	 * @param string		directory of the pid file
	 */
	public static function singleton($file, $dir = '/tmp') {
		if (empty(self::$_instance)) {
			self::$_instance = new Pid($dir . '/' . $file);	
		}
		return self::$_instance;
	}


	/**
	 * Remove the ProcessID file
	 */
	public function remove() {
		if ($this->_isCreated) {
			// close handle to file and remove it
			fclose($this->_fp);
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

		if ($this->_fp = fopen($this->_pidFile, "x")) {
			if (!flock($this->_fp, LOCK_EX | LOCK_NB)) {
				fclose($this->_fp);
				return FALSE;
			}
		} else { 
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
		return $this->_isRemoved;
	}

	/**
	 * Get the file name (location) of the pid file
	 *
	 * @param string
	 */
	public function getFileName() {
		return $this->_pidFile;
	}
}
