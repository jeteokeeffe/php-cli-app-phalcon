<?php

/**
 * Cli based Application
 *
 * @author Jete O'Keeffe
 * @version 1.0
 * @package cli
 */

namespace application;

use \Cli\Output as Output;

class Cli extends \Phalcon\Cli\Console {

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
	 * @var int type of instance (single instance at a time or multiple)
	 */
	protected $_mode;

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
	 * @const
	 */
	const SINGLE_INSTANCE = 1;

	/**
	 * @const FLAG_DEBUG
	 */
	const FLAG_DEBUG = '--debug';

	/**
	 * @const FLAG_SINGLE
	 */
	const FLAG_SINGLE = '--single';

	/**
	 * @const FLAG_NO_RECORD
	 */
	const FLAG_NO_RECORD = '--no-record';

	/**
	 * constructor
	 *
	 * @param single instance or multiple
	 * @param directory of the pid file
	 */
	public function __construct($mode = 0, $pidDir = '/tmp') {

		$this->_mode = $mode;
		$this->_pidDir = $pidDir;
		$this->_stderr = $this->_stdout = '';
		$this->_isRecording = TRUE;
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
                        return new \Phalcon\Db\Adapter\Pdo\Mysql(array(
                                'host' => $di->get('config')->database->host,
                                'username' => $di->get('config')->database->username,
                                'password' => $di->get('config')->database->password,
                                'dbname' => $di->get('config')->database->name
                        ));
                });
                $this->setDI($di);
        }


        /**
         * Set namespaces to tranverse through in the autoloader
         *
         * @link http://docs.phalconphp.com/en/latest/reference/loader.html
         * @throws Exception
         * @param string $file          map of namespace to directories
         */
        public function setAutoload($file, $dir) {
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
			$this->preTaskCheck($this->_argv, $this->_argc);

			// Check Instance Mode (can only one run at a time)
			$this->checkProcessInstance($this->_argv);

			// Add Record to DB that task started
			if ($this->_isRecording) {
				$task = new \Models\Task();
				$taskId = $task->insertTask();
			}

			// Setup args for console
			$args['task'] = 'Tasks' . "\\" . $this->_argv[1];
			$args['action'] = $this->_argv[2];
			$args['params'] = '';

			// Kick off Task
			$this->handle($args);

			$this->removeProcessInstance();

			// Update status
			if ($this->_isRecording) {
				$task->updateSuccessful($taskId, Output::getStdout(), Output::getStderr(), $exit);
			}


		} catch(\cli\Exception $e) {
			$exit = 3;
			$this->handleException($e, $taskId, $exit);

		} catch(\Phalcon\Exception $e) {
			$exit = 2;
			$this->handleException($e, $taskId, $exit);

		} catch(\Exception $e) {
			$exit = 1;
			$this->handleException($e, $taskId, $exit);
		}


		// Display Debug info if enabled
		if ($this->_isDebug === TRUE) {
			$this->displayDebug($this->getDI()->getShared('dispatcher'));
		}

		return $exit;
	}
	

	/**
	 * Check if pid file needs to be created
	 *
	 * @param array $argv	cli arguments
	 */
	public function checkProcessInstance(&$argv) {

			//	Setup CLI Application flags
		foreach($argv as $num => $arg) {
			if ($arg == Cli::FLAG_DEBUG) {
					//	Make sure all errors display
				error_reporting(E_ALL);
				ini_set('display_errors', 1);

				$debug = TRUE;
				unset($argv[$num]);
			} else if ($arg == Cli::FLAG_SINGLE) {
				unset($argv[$num]);
				$mode = Cli::SINGLE_INSTANCE;
			} else if ($arg == Cli::FLAG_NO_RECORD) {
				$this->_isRecording = FALSE;
				unset($argv[$num]);
			}
		}
			//	Single Instance
		if ($this->_mode == self::SINGLE_INSTANCE) {

			foreach($argv as $arg) {
				if (empty($launcher)) {
					$newArgv[] = $launcher = $arg;
				} else if (empty($task)) {
					$newArgv[] = $task = $arg;
				} else if (empty($action)) {
					$newArgv[] = $action = $arg;
				}
			}
				//		
			$action = empty($action) ? 'main' : $action;
			$argv = $newArgv;

			$pidFile = $this->_pidFile = sprintf('%s/%s-%s.pid', $this->_pidDir, $task, $action);

				//	Make sure only 1 app at a time is running
			if (file_exists($pidFile)) {
				throw new \Exception('Instance of task is already running', self::ERROR_SINGLE);
			} else {

					//	Create PID File
				if (!file_put_contents($pidFile, getmypid())) {
					$desc = '';
					throw new \exceptions\System('unable to create pid file', $desc);
				}

				if ($this->_isDebug) {
					Output::stdout("[DEBUG] Created Pid File: $this->_pidFile");
				}
			}
		}
	}

	/**
	 * Remove Pid File
	 *
	 * return bool
	 */
	public function removeProcessInstance() {
		if ($this->_mode == self::SINGLE_INSTANCE) {
			$result = unlink($this->_pidFile);
			if ($result && $this->_isDebug) {
				Output::stdout("[DEBUG] Removed Pid File: $this->_pidFile");
			} else if ($result === FALSE) {
				Output::stderr("[ERROR] Failed to remove Pid File: $this->_pidFile");
			}

			return $result;
		}

		return TRUE;
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
	protected function preTaskCheck($argv, $argc) {

		// Make sure task is added
		if ($argc < 2) {
			throw new \Exception('Bad Number of Params needed for script');
		} 
	}

	/**
	 * sets debug mode
	 *
	 * @param $isOn bool 
	 */
	public function setDebugMode($debug) {
		$this->_isDebug = $debug;
	}


	/**
	 * handle script ending exception
	 *
	 * @param Exception that caused the failure
	 * @param id of the task you started
	 * @param exit code status of the process
	 */
	protected function handleException(\Exception $e, $taskId, $exit) {

		$sub = '%s[ERROR] \e[0m%s file: %s line: %d';

			//	Remove Process Instance
		if ($e->getCode() != self::ERROR_SINGLE) {
			$this->removeProcessInstance();
		}

			//	Update Failure
		if ($this->_isRecording && $taskId > 0) {
			
			if ($error = class_exists('\\Cli\Output')) {
				$msg = sprintf($sub, Output::COLOR_RED, $e->getMessage(), $e->getFile(), $e->getLine());

				$stdout = Output::getStdout();
				$stderr = Output::getStderr();
			} else { 
				$msg = sprintf($sub, '\e[0;31m', $e->getMessage(), $e->getFile(), $e->getLine());

				$stdout = '';
				$stderr = $msg;
			}

				//	Update Task w/ error messages
			$task = new \Models\Task();
			$task->updateFailed($taskId, $stdout, $stderr, $exit);
		} else {
			$msg = $e->getMessage();
		}

			//	Let user that ran this know it failed
		if (class_exists('\\Cli\Output')) {
			Output::stderr($msg);
		} else {
			fwrite(STDERR, $msg . PHP_EOL);
		}
	}

	/**
	 * Debug for the application
	 *
	 * @param
	 */
	public function displayDebug($dispatcher) {
		
		$taskName = $dispatcher->getTaskName();
		$actionName = $dispatcher->getActionName();

		if (!class_exists('\\cli\Output')) {
			fwrite(STDERR, "Unable to find Output class" . PHP_EOL);
			return;
		}

		Output::stdout("");
		Output::stdout("--------------DEBUG ENABLED---------------------");
		Output::stdout("total time: " . (microtime(TRUE) - $_SERVER['REQUEST_TIME'] ));
		Output::stdout("hostname: " . php_uname('n'));
		Output::stdout("pid: " . getmypid());

		if ($this->_mode == Application::SINGLE_INSTANCE) {	
			Output::stdout("pid file: " . $this->_pidFile );
		}

		Output::stdout("task: $taskName");
		Output::stdout("action: $actionName");
		Output::stdout("");

			//	Print out Commands
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

			//	Print out Exceptions
		$exceptions = Logger::getInstance()->getAll();
		if (!empty($exceptions)) {
			Output::stdout(Output::COLOR_BLUE . "+++Exceptions+++" . Output::COLOR_NONE);
			foreach($exceptions as $except) {
				Output::stdout($except->getMessage());		
				Output::stdout($except->getCode() . "\t" . $except->getFile() . "\t" . $except->getLine());
				Output::stdout("");
			}
			Output::stdout("");
		}

			//	Print out Queries
		$queries = DatabaseFactory::getQueries();
		if (!empty($queries)) {
			Output::stdout(Output::COLOR_BLUE . "+++MySQL Queries+++" . Output::COLOR_NONE);
			foreach($queries as $query) {
				Output::stdout($query->query);		
				Output::stdout($query->file . "\t" . $query->line . "\t" . ($query->success ? 
					Output::COLOR_GREEN . "Success" . Output::COLOR_NONE : 
						Output::COLOR_RED . "Failed" . Output::COLOR_NONE));	
				Output::stdout("");
			}
			Output::stdout("");
		}

			//	Print out Memcached
		if (class_exists('MemcachedCache', FALSE)) {
			Output::stdout(Output::COLOR_BLUE . "+++Memcached+++" . Output::COLOR_NONE);
			$cache = MemcachedCache::singleton();
			foreach($cache->getServerList() as $server) {
			}
		}
	}
}
