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
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'logpx.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'logp.php';

/**
 * measure duration time of executing of part of application
 * new logx, new logy write to duration directory
 */
class logpy extends logpx
{
    public function __construct($name = '')
    {
        $number = (int)preg_replace('/[^0-9]/', '', __CLASS__);
        $duration = round(microtime(true) * 1000) - self::$_data[$name . $number];
        self::$_data[$name . $number] = null;
        $name ? new logp($name . ': ' . $duration) : new logp($duration);
    }
}