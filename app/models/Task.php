<?php

namespace Models;

class Task extends \Phalcon\Mvc\Model
{

    public function initialize()
    {
        $this->setSource("task");
    }

    /**
     * @param string $scriptName $_SERVER['PHP_SELF'] name
     * @return FALSE|int
     */
    public function insertTask($scriptName)
    {

        $this->script_name = PHP_BINARY . ' ' . $scriptName;
        $this->params      = implode(' ', $_SERVER['argv']);
        $this->server_name = php_uname('n');
        $this->server_user = get_current_user();
        $this->start_time  = date('Y-m-d H:i:s');
        $this->pid         = getmypid();

        if ($this->save()) {
            return $this->task_id;
        }

        return false;
    }


    /**
     * @param int $taskId id of the task you started
     * @param string $stdout
     * @param string $stderr
     * @param int $status exit status code
     * @return bool
     */
    public function updateFailed($taskId, $stdout, $stderr, $status)
    {
        $state = $status === 0 ? 'SUCCESSFUL' : 'FAILED';

        $this->task_id     = $taskId;
        $this->exit_status = $status;
        $this->state       = $state;
        $this->stdout      = $stdout;
        $this->stderr      = $stderr;
        $this->stop_time   = date('Y-m-d H:i:s');

        return $this->update();
    }

    /**
     * @param int $taskId id of the task you started
     * @param string $stdout
     * @param string $stderr
     * @param int $status exit status code
     * @return bool
     */
    public function updateSuccessful($taskId, $stdout, $stderr, $status)
    {
        $state = $status == 0 ? 'SUCCESSFUL' : 'FAILED';

        $this->taskId      = $taskId;
        $this->exit_status = $status;
        $this->state       = $state;
        $this->stdout      = $stdout;
        $this->stderr      = $stderr;
        $this->stop_time   = date('Y-m-d H:i:s');

        return $this->update();
    }
}
