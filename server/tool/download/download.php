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
// run forever
ignore_user_abort(true);
set_time_limit(0);

// include library
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'index.php';
scanPhpFile(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'constant');
scanPhpFile(__FILE__, $excepts = 'youtube.php');

// get config path
firstValidFilePath(CONFIG_SOURCE_PATH . DIRECTORY_SEPARATOR . basename(__FILE__, PHP_EXTENSION) . CONFIG_EXTENSION, $downloadConfigPath);

// convert to array
file2config($downloadConfigPath, $downloadConfig);
// download items
download($downloadConfigPath, $downloadConfig);

 