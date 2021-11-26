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
 * refer logr
 * each calling output in a file
 * each request output in a directory
 * base on SERVER REQUEST URI and uniqid
 */
class logcall extends lig
{
    public function __construct()
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);
        $className = get_class($this);
        $expressions = func_get_args();
        $expressionNames = array();
        $count = count($expressions);
        $requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        $expressionNames[] = '$requestUri';
        
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
        $length = strlen('123456' . lig::$___uniqid . $prefix);
        if (255 <= $length) {
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
        $prefix === $rPrefix || $prefix = $rPrefix . '/_'; # treat as directory if end by directory separator

        # extract directory and file name
        $pathInfo = pathinfo($prefix);
        # 255 is max length of file name
        $requestUri = substr($requestUri, 0, MAX_LENGTH_FILE_NAME - $length);

        # set directory and file name
        if (lig::$___customPrefix) {
            lig::setLogDir($pathInfo['dirname'] . '_' . lig::$___uniqid);
            lig::setLogFileName($pathInfo['basename'] . '_' . (lig::$___order++) . '_' . $requestUri);
        } else {
            lig::setLogFileName(Stack::getFirstClass() . lig::$___uniqid . '_' . (lig::$___order++) . '_' . $requestUri);
        }

        eval('parent::__construct(' . implode(',', $expressionNames) . ');');
    }
}
 
