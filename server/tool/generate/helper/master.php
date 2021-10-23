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
require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'core.php';

/**
 * get master data
 */
function getMasters()
{
    $masters = array();

    // validate dir
    firstValidDirPath(CONFIG_SOURCE_PATH . DIRECTORY_SEPARATOR . 'master', $masterPath);
    $files = scanPhpFile($masterPath, EMPTY_STRING, true, false);
    foreach ($files as $file) {
        $table = basename($file, '.php');
        $masters[$table] = require_once $file;
    }

    return $masters;
}
 
