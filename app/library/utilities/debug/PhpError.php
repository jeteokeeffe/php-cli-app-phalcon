<?php

/**
 * Capture PHP related warnings/errors
 *
 * @package    Utilties
 * @subpackage Debug
 * @author     Jete O'Keeffe
 * @version    1.0
 * @link
 */

namespace Utilities\Debug;

use \Models\RuntimeError;

class PhpError
{


    /**
     * Record any warnings/errors by php
     *
     * @param int $errNo      php error number
     * @param string $errStr  php error description
     * @param string $errFile php file where the error occured
     * @param int $errLine    php line where the error occured
     * @return false
     */
    public static function errorHandler($errNo, $errStr, $errFile, $errLine)
    {

        if ($errNo != E_STRICT) {

            // Get Remote Ip or CLI script?
            if (PHP_SAPI == 'cli') {
                $script = $_SERVER['PHP_SELF'];
                $ip     = 'CLI';
            } else {
                $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
                $script   = $protocol . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
                $ip       = $_SERVER['REMOTE_ADDR'];
            }

            $rt                   = new RuntimeError();
            $rt->title            = $errStr;
            $rt->file             = $errFile;
            $rt->line             = $errLine;
            $rt->error_type       = $errNo;
            $rt->server_name      = php_uname('n');
            $rt->execution_script = $script;
            $rt->pid              = getmypid();
            $rt->ip_address       = $ip;
            $rt->save();
        }

        return false;
    }

    /**
     * Capture any errors at the end script (especially runtime errors)
     */
    public static function runtimeShutdown()
    {
        $e = error_get_last();
        if (!empty($e)) {
            // Record Error
            PhpError::errorHandler($e['type'], $e['message'], $e['file'], $e['line']);
        }
    }
}
