<?php
/**
 * 
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license, please send an email
 * to vinhnv@live.com so i can send you a copy immediately.
 *
 * @copyright Copyright (c) 2011-2015 Nguyen Van Vinh (vinhnv@live.com)
 */
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lig.php';

/**
 * each request each file (in a directory, optional)
 * base on SERVER REQUEST URI and uniqid
 */
class logr extends lig
{
    /** r not extend */
    static $_first = true;
    
    public function __construct()
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);
        $className = get_class($this);
        $expressions = func_get_args();
        $expressionNames = array();
        $count = count($expressions);
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] . '-' : '';
        if('CLI' == strtoupper(PHP_SAPI)) {
            $requestUri = isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : '';
        } else {
            $requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        }
        if(static::$_first) {
            $expressionNames[] = '$requestUri';
            static::$_first = false;
        }
        switch (true) {
            case 0 === $count:
                $expressions = array_merge(array('_error_' . __CLASS__ . '0param'), $expressions);
                break;
            case lig::$___customPrefix && 1 === $count:
                $expressions = array_merge(array('_error_' . __CLASS__ . '1param'), $expressions);
                break;
            case lig::$___customPrefix && !is_string($expressions[0]):
                $expressions = array_merge(array('_error_' . __CLASS__ . 'nostringparam'), $expressions);
                break;
            default:
                break;
        }
        $prefix = '';
        foreach ($expressions as $index => $arg) {
            // set prefix or project name
            if (lig::$___customPrefix && 0 === $index) {
                $prefix = $expressions[0];
                continue;
            }

            $expressionNames[] = '$expressions[' . $index . ']';
        }
        # if prefix join unique id of which length greater than 255, then there is an error
        if (255 <= strlen($prefix . lig::$___uniqid)) {
            // backup $prefix and append it to $expressionNames as data than prefix of log name
            $prefixBackup = $prefix;
            $expressionNames = array_merge(array('$prefixBackup'), $expressionNames);

            // set new value for $prefix
            $prefix = '_error_' . __CLASS__ . '3';
        }

        # prefix can contain directory
        $prefix = preg_replace(LOG_PREFIX_INVALID_CHARACTER, DEFAULT_REPLACEMENT, $prefix);

        $prefix = ltrim($prefix, '/\\');
        $rPrefix = rtrim($prefix, '/\\');
        $prefix === $rPrefix || $prefix = $rPrefix . '/_';
        $pathInfo = pathinfo($prefix);
        # 255 is max length of file name
        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'cli';
        $requestUri = substr(DEFAULT_REPLACEMENT . $method . $requestUri, 0, MAX_LENGTH_FILE_NAME - strlen($prefix . lig::$___uniqid));
        if (lig::$___customPrefix) {
            lig::setLogDir($pathInfo['dirname']);
            lig::setLogFileName($pathInfo['basename'] . lig::$___uniqid . $requestUri);
        } else {
            lig::setLogFileName($host . Stack::getFirstClass() . lig::$___uniqid . $requestUri);
        }

        eval('parent::__construct(' . implode(',', $expressionNames) . ');');
    }
}
 
