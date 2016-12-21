<?php

namespace Unimatrix\Utility\View\Helper;

use Cake\View\Helper;

/**
 * Debug
 * Contains useful debug functions
 *
 * Load:
 * ---------------------------------------------------
 * This helper must be loaded in your View/AppView.php
 * before you can use it
 *
 * $this->loadHelper('Unimatrix/Utility.Debug');
 *
 * Usage:
 * ---------------------------------------------------
 * $this->Number->precision($this->Debug->requestTime() * 1000, 0)
 *
 * @author Flavius
 * @version 0.1
 */
class DebugHelper extends Helper
{
    /**
     * Get the total execution time until this point
     *
     * @return float elapsed time in seconds since script start.
     */
    public static function requestTime()
    {
        $start = self::requestStartTime();
        $now = microtime(true);

        return ($now - $start);
    }

    /**
     * get the time the current request started.
     *
     * @return float time of request start
     */
    public static function requestStartTime() {
        if(defined('TIME_START')) $startTime = TIME_START;
        elseif(isset($GLOBALS['TIME_START'])) $startTime = $GLOBALS['TIME_START'];
        else $startTime = env('REQUEST_TIME');

        return $startTime;
    }
}