<?php

/**
 * Handling of the ProcessID(Pid) file's creation, removal and existance
 *
 * @package Cli
 * @author  Jete O'Keeffe
 */

namespace Cli;

class Pid
{

    /**
     * @var bool
     */
    protected $_isCreated;

    /**
     * @var bool
     */
    protected $_isRemoved;

    /**
     * @var self
     */
    protected static $_instance;

    /**
     * File pointer
     * @var resource
     */
    protected $_fp;

    /**
     *
     */
    protected function __construct($file)
    {
        $this->_pidFile   = $file;
        $this->_isCreated = false;
        $this->_isRemoved = false;
        $this->_fp        = null;
    }

    /**
     * Get instance
     *
     * @param string $file name of the pid file
     * @param string $dir  of the pid file
     * @return Pid instance
     */
    public static function singleton($file, $dir = '/tmp')
    {
        if (empty(self::$_instance)) {
            self::$_instance = new Pid($dir . '/' . $file);
        }

        return self::$_instance;
    }


    /**
     * Remove the ProcessID file
     * @return bool
     */
    public function remove()
    {
        if ($this->_isCreated) {
            // close handle to file and remove it
            fclose($this->_fp);
            if ($result = unlink($this->_pidFile)) {
                return $this->_isRemoved = true;
            }
        }

        return false;
    }


    /**
     * Create the ProcessID file
     * @throws \Exception
     */
    public function create()
    {
        if ($this->exists()) {
            throw new \Exception('Pid File exists/Instance of script is already running');
        }

        if (is_writable($this->_pidFile)) {
            throw new \Exception('Unable to write to this file');
        }
        if (file_exists($this->_pidFile) && $this->_fp = fopen($this->_pidFile, "x")) {
            if (!flock($this->_fp, LOCK_EX | LOCK_NB)) {
                fclose($this->_fp);

                return false;
            }
        } else {
            return false;
        }

        $this->_isCreated = true;

        return true;
    }

    /**
     * Check if the PID file exists
     * @return bool
     */
    public function exists()
    {
        return file_exists($this->_pidFile);
    }

    /**
     * Check if Pid has been created
     * @return bool
     */
    public function created()
    {
        return $this->_isCreated;
    }

    /**
     * Check if Pid file has been deleted
     * @return bool
     */
    public function removed()
    {
        return $this->_isRemoved;
    }

    /**
     * Get the file name (location) of the pid file
     *
     * @param string
     */
    public function getFileName()
    {
        return $this->_pidFile;
    }
}
