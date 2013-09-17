<?php

/**
 * Cli based Application used to setup Tasks to run
 *
 * @author Jete O'Keeffe
 * @version 1.0
 * @package Cli
 */

namespace Application;

use \Cli\Output as Output;
use \Interfaces\IRun as IRun;

class Cli extends \Phalcon\Cli\Console implements IRun {

	/**
	 * @const
	 */
	const ERROR_SINGLE = 999;

	/**
	 * @var string filename that will store the pid 
	 */
	protected $_pidFile;

	/**
	 * @var string directory path that will contain the pid file
	 */
	protected $_pidDir = '/tmp';

	/**
	 * @var bool	if debug mode is on or off
	 */
	protected $_isDebug;

	/**
	 * @var bool 	whether to record tasks by inserting into the database or not
	 */
	protected $_isRecording;

	/**
	 * @var
	 */
	protected $_argc;

	/**
	 * @var
	 */
	protected $_argv;

	/**
	 * @var
	 */
	protected $_isSingleInstance;

	/**
	 * Task for cli handler to run
	 */
	protected $_task;
	/**
	 * Action for cli handler to run
	 */
	protected $_action;

	/**
	 * Parameters to be passed to Task
	 * @var
	 */
	protected $_params;

	/**
	 * Task Id from the database
	 * @var
	 */
	protected $_taskId;

	/**
	 * constructor
	 *
	 * @param directory of the pid file
	 */
	public function __construct($pidDir = '/tmp') {
		$this->_pidDir = $pidDir;
		$this->_stderr = $this->_stdout = '';
		$this->_isSingleInstance = $this->_isRecording = FALSE;
		$this->_task = $this->_action = NULL;
		$this->_params = array();
		$this->_taskId = NULL;
	}

        /**
         * Set Dependency Injector with configuration variables
         *
         * @throws Exception
         * @param string $file          full path to configuration file
         */
        public function setConfig($file) {

                if (!file_exists($file)) {
                        throw new \Exception('Unable to load configuration file');
                }
			
		$di = new \Phalcon\DI\FactoryDefault\CLI();
                $di->set('config', new \Phalcon\Config(require $file));

		$di->set('db', function() use ($di) {

			$type = strtolower($di->get('config')->database->adapter);
			$creds = array(
				'host' => $di->get('config')->database->host,
				'username' => $di->get('config')->database->username,
				'password' => $di->get('config')->database->password,
				'dbname' => $di->get('config')->database->name
			);

			if ($type == 'mysql') {
				$connection =  new \Phalcon\Db\Adapter\Pdo\Mysql($creds);
			} else if ($type == 'postgres') {
				$connection =  new \Phalcon\Db\Adapter\Pdo\Postgesql($creds);
			} else if ($type == 'sqlite') {
				$connection =  new \Phalcon\Db\Adapter\Pdo\Sqlite($creds);
			} else {
				throw new Exception('Bad Database Adapter');
			}

			$connection->setEventsManager(new \Events\Database\Profile());

			return $connection;
		});

                $this->setDI($di);
        }


        /**
         * Set namespaces to tranverse through in the autoloader
         *
         * @link http://docs.phalconphp.com/en/latest/reference/loader.html
         * @throws Exception
         * @param string $file          map of namespace to directories
	 * @param string $appDir	location of the app directory
         */
        public function setAutoload($file, $appDir) {
                if (!file_exists($file)) {
                        throw new \Exception('Unable to load autoloader file');
                }

                // Set dir to be used inside include file
                $namespaces = include $file;

                $loader = new \Phalcon\Loader();
                $loader->registerNamespaces($namespaces)->register();
        }


	/**
 	 * Set Application arguments
	 *
	 * @param array of arguments
	 * @param count of arguments
	 */
	public function setArgs($argv, $argc) {
		$this->_argv = $argv;
		$this->_argc = $argc;
	}


	/**
         * Set events to be triggered before/after certain stages in Micro App
         *
         * @param object $event         events to add
         */
        public function setEvents(\Phalcon\Events\Manager $events) {
                $this->setEventsManager($events);
        }


	/**
	 * kick off the task
	 */
	public function run() {

		try {
			$exit = 0;
			$taskId = NULL;

			// Check on stupid mistakes
			$this->preTaskCheck($this->_argc);

			$this->determineTask($this->_argv);
			// Check Instance Mode (can only one run at a time)
			$this->checkProcessInstance($this->_argv);

			// Add Record to DB that task started
			if ($this->_isRecording) {
				$task = new \Models\Task();
				$this->_taskId = $task->insertTask($_SERVER['PHP_SELF']);
			}

			// Setup args (task, action, params) for console
			$args['task'] = 'Tasks' . "\\" . $this->_task;
			$args['action'] = !empty($this->_action) ? $this->_action : 'main';
			if (!empty($this->_params)) {
				$args['params'] = $this->_params;
			}

			// Kick off Task
			$this->handle($args);

			$this->removeProcessInstance();

			// Update status
			if ($this->_isRecording) {
				$task->updateSuccessful($this->_taskId, Output::getStdout(), Output::getStderr(), $exit);
			}


		} catch(\cli\Exception $e) {
			$exit = 3;
			$this->handleException($e, $this->_taskId, $exit);

		} catch(\Phalcon\Exception $e) {
			$exit = 2;
			$this->handleException($e, $this->_taskId, $exit);

		} catch(\Exception $e) {
			$exit = 1;
			$this->handleException($e, $this->_taskId, $exit);
		}

		return $exit;
	}
	

	/**
	 * Check if pid file needs to be created
	 *
	 * @param array $argv	cli arguments
	 */
	public function checkProcessInstance($argv) {

		// Single Instance
		if ($this->isSingleInstance()) {

			$file = sprintf('%s-%s.pid', $this->_task, $this->_action);
			$pid = \Cli\Pid::singleton($file);

			// Default
			$this->_pidFile = $file;

			// Make sure only 1 app at a time is running
			if ($pid->exists()) {
				throw new \Exception('Instance of task is already running', self::ERROR_SINGLE);
			} else {
				// Create PID File
				/*
				$fp = fopen($this->_pidFile, 'r+');
				if (!flock($fp, LOCK_EX | LOCK_NB)) {
					fclose($fp);
					throw new \exceptions\System('unable to create pid file', $desc);
				}*/

				if ($pid->create()) {
					if ($this->isDebug()) {
						Output::stdout("[DEBUG] Created Pid File: " . $pid->getFileName());
					}
				} else {
					$desc = '';
					throw new \exceptions\System('unable to create pid file', $desc);
				}

				/*if (!file_put_contents($this->_pidFile, getmypid())) {
					$desc = '';
					throw new \exceptions\System('unable to create pid file', $desc);
				}*/

			}
		}
	}

	/**
	 * Remove Pid File
	 *
	 * @return bool
	 */
	public function removeProcessInstance() {
		if ($this->isSingleInstance()) {
			$pid = \Cli\Pid::singleton('');
			if ($pid->created() && !$pid->removed()) {
				if ($result = $pid->remove()) {
					if ($this->isDebug()) {
						Output::stdout("[DEBUG] Removed Pid File: " . $pid->getFileName());
					}
				} else {
					$msg = Output::COLOR_RED . "[ERROR]" . Output::COLOR_NONE . " Failed to remove Pid File: $this->_pidFile";
					Output::stderr($msg);
				}
				return $result;
			}
		}

		return TRUE;
	}

	/**
	 * Get the task/action to direct to
	 *
	 * @param array $flags	cli arguments to determine tasks/action/param
	 * @throws Exception
	 */
	protected function determineTask($flags) {

		// Since first argument is the name so script executing (pop it off the list)
		array_shift($flags);

		if (is_array($flags) && !empty($flags)) {
			foreach($flags as $flag) {
				if (empty($this->_task) && !$this->isFlag($flag)) {
					$this->_task = $flag;
				} else if (empty($this->_action) && !$this->isFlag($flag)) {
					$this->_action = $flag;
				} else if (!$this->isFlag($flag)) {
					$this->_params[] = $flag;
				}
			}
		} else {
			throw new Exception('Unable to determine task/action/params');
		}
	}

	/**
	 * set mode of multiple or single instance at a time
	 *
	 * @param int $mode 
	 */
	public function setMode($mode) {
		$this->_mode = $mode;
	}

	/**
	 * set the directory location of the pid file
	 *
	 * @param $dir string
	 */
	public function setPidDir($dir) {
		$this->_pidDir = $dir;
	}


	/**
	 * make sure everything required is setup before starting the task
	 *
	 * @param array $argv array of arguments
	 * @param int $argc count of arguments
	 * @throws Exception
	 */
	protected function preTaskCheck($argc) {

		// Make sure task is added
		if ($argc < 2) {
			throw new \Exception('Bad Number of Params needed for script');
		} 
	}

	/**
	 * sets debug mode
	 *
	 * @param bool $debug
	 */
	public function setDebug($debug) {
		$this->_isDebug = $debug;
		if ($debug) {
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
		}
	}

	public function isDebug() {
		return $this->_isDebug;
	}

	/**
	 * Set Application to record results to database
	 *
	 * @param bool
	 */
	public function setRecording($record) {
		$this->_isRecording = $record;
	}

	/**
	 * Determine if application is recording output (stdout and stderr) to database
	 *
	 * @return bool
	 */
	public function isRecording() {
		return $this->_isRecording;
	}

	/**
	 * Set Single Instance Flag
	 *
	 * @param bool
	 */
	public function setSingleInstance($single) {
		$this->_isSingleInstance = $single;
	}

	/**
	 * determine if only a single instance is allowed to run
	 *
	 * @return bool
	 */
	public function isSingleInstance() {
		return $this->_isSingleInstance;
	}


	/**
	 * handle script ending exception
	 *
	 * @param Exception that caused the failure
	 * @param id of the task you started
	 * @param exit code status of the process
	 */
	protected function handleException(\Exception $e, $taskId, $exit) {

		$sub = '%s[ERROR]%s %s file: %s line: %d';

		// Remove Process Instance
		if ($e->getCode() != self::ERROR_SINGLE) {
			$this->removeProcessInstance();
		}

		// Update Failure
		if ($this->_isRecording && $taskId > 0) {
			$stdout = Output::getStdout();
			$stderr = Output::getStderr();
				//	Update Task w/ error messages
			$task = new \Models\Task();
			$task->updateFailed($taskId, $stdout, $stderr, $exit);
		} 

		$msg = sprintf($sub, Output::COLOR_RED, Output::COLOR_NONE, $e->getMessage(), $e->getFile(), $e->getLine());

		// Let user that ran this know it failed
		Output::stderr($msg);
	}

	/**
	 * Determine if argument is a special flag
	 *
	 * @param string
	 * @return bool
	 */
	protected function isFlag($flag) {
		return substr(trim($flag), 2) == '--';
	}

	/**
	 * Get the PID File location
	 * @return string
	 */
	public function getPidFile() {
		return $this->_pidFile;
	}

	/**
	 * Get the auto incremented value for current task
	 * @return int|NULL
	 */
	public function getTaskId() {
		return $this->_taskId;
	}
}
