<?php

namespace Models;

class Task extends \Phalcon\Mvc\Model {

	public function initialize() {
		$this->setSource("task");
	}

	/**
	 * @param
	 * @return FALSE|int
	 */
	public function insertTask($scriptName) {

		$this->script_name = $_SERVER['_'] . ' ' . $scriptName;
		$this->params = implode(' ', $_SERVER['argv']);
		$this->server_name = php_uname('n');
		$this->server_user = get_current_user();
		$this->start_time = date('Y-m-d H:i:s');
		$this->pid = getmypid();

		if ($this->save()) {
			return $this->task_id;
		}

		return FALSE;
	}


	/**
	 * @param
	 * @param
	 * @param
	 * @param
	 * @return bool
	 */
	public function updateFailed($taskId, $stdout, $stderr, $status) {
		$state = $status === 0 ? 'SUCCESSFUL' : 'FAILED';

		$this->task_id = $taskId;
		$this->exit_status = $status;
		$this->state = $state;
		$this->stdout = $stdout;
		$this->stderr = $stderr;
		$this->stop_time = date('Y-m-d H:i:s');

		return $this->update();
	}

	/**
	 * @param
	 * @param
	 * @param
	 * @param
	 * @return
	 */
	public function updateSuccessful($taskId, $stdout, $stderr, $status) {
		$state = $status == 0 ? 'SUCCESSFUL' : 'FAILED';

		$this->exit_status = $status;
		$this->state = $state;
		$this->stdout = $stdout;
		$this->stderr = $stderr;
		$this->stop_time = date('Y-m-d H:i:s');

		return $this->update();
	}
}
