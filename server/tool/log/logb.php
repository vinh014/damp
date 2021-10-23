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

/**
 * set a break point when one of conditions is true
 */
class logb
{
    public function __construct()
    {
        $conditions = func_get_args();
        $break = false;
        $count = count($conditions);
        for($i = 0; $i < $count; $i++) {
            if($conditions[$i]) {
                $break = true;
                break;
            }
        }
        $break && function_exists('xdebug_break') && xdebug_break();
    }
}