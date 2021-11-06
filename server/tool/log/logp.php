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
 * not write log data to file but showing
 */
class logp extends lig
{
    public function __construct()
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);
        $className = get_class($this);
        $expressions = func_get_args();
        // add a param in cli mode. The tool auto insert a new line
        'CLI' == strtoupper(php_sapi_name()) && $expressions[] = '';
        $expressionNames = array();
        foreach ($expressions as $index => $arg) {
            $expressionNames[] = '$expressions[' . $index . ']';
        }
        eval($className . '::onlyEcho(true);');
        eval('parent::__construct(' . implode(',', $expressionNames) . ');');
    }
}
 
