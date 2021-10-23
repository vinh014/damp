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
 * @desc constant alert message
 * @tutorial string can contain params ex. %s, %d for function sprintf
 */

define('MESSAGE_PATH_OF_LOG_FILE', 'The path of log file: "%s"');

/**
 * @desc constant logic
 */
# the variable, that prefix by this, that isn't reset
# if it's changed, relation must be changed
define('PREFIX_NOT_RESET', '___');

define('DATE_TIME_FORMAT2', 'Y-m-d H:i:s');