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
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'index.php';

scanPhpFile(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'constant', EMPTY_STRING, TRUE);
scanPhpFile(__FILE__, EMPTY_STRING, TRUE);

// total created records of each table
$globalTotalRecord = array();
// base count or id of first record of each table at current thread
$baseCount = array();
// max count of each table at current thread
$maxCount = array();
// plain file name not extension
$filename = basename(__FILE__, PHP_EXTENSION);
// list allow keys, anti override exist variable i.e filename|[]| will override $filename
$supportConfigs = array_flip(array(
    ADDITIONAL_SEED_KEY,
    FUNCTION_CONFIG_ISSUE_KEY, FUNCTION_CONFIG_PROJECT_KEY,
    ORACLE_USERNAME_KEY, ORACLE_PASSWORD_KEY, PROJECT_NAME_KEY,
    RECORD_FOR_LOG_KEY, RECORD_PER_STEP_KEY, RECORD_PER_THREAD_KEY,
    RATE_OF_NULL_KEY, THREAD_KEY, TIMEZONE_KEY, TIME_STEP_KEY, TIME_LIMIT_KEY,
));

// validate config file
firstValidFilePath(CONFIG_SOURCE_PATH . DIRECTORY_SEPARATOR . $filename . CONFIG_EXTENSION, $toolConfigPath);
if (empty($toolConfigPath)) {
    echo sprintf(MESSAGE_NO_CONFIG_FOR, 'generate') . PHP_EOL;
    exit;
}
echo sprintf(MESSAGE_PATH_OF_TOOL_CONFIG, $toolConfigPath) . PHP_EOL;
// convert config to array
file2config($toolConfigPath, $toolConfigs);
$toolConfigs = array_intersect_key($toolConfigs, $supportConfigs); # only accept allowed keys
extract($toolConfigs, EXTR_OVERWRITE); # create variables
// config time
set_time_limit(${TIME_LIMIT_KEY});
date_default_timezone_set(${TIMEZONE_KEY});

// define constant base variable that is created by key constant and config file
define('PROJECT_NAME', isset(${PROJECT_NAME_KEY}) ? ${PROJECT_NAME_KEY} : '');
define('THREAD', isset(${THREAD_KEY}) && (int)${THREAD_KEY} > 0 ? (int)${THREAD_KEY} : 0);
define('RECORD_PER_THREAD', isset(${RECORD_PER_THREAD_KEY}) && (int)${RECORD_PER_THREAD_KEY} > 0 ? (int)${RECORD_PER_THREAD_KEY} : 0);
define('ORACLE_USERNAME', isset(${ORACLE_USERNAME_KEY}) ? ${ORACLE_USERNAME_KEY} : '');
define('ORACLE_PASSWORD', isset(${ORACLE_PASSWORD_KEY}) ? ${ORACLE_PASSWORD_KEY} : '');
define('RATE_OF_NULL', isset(${RATE_OF_NULL_KEY}) ? (float)${RATE_OF_NULL_KEY} : 0.0);
define('RECORD_PER_STEP', isset(${RECORD_PER_STEP_KEY}) && (int)${RECORD_PER_STEP_KEY} > 0 ? (int)${RECORD_PER_STEP_KEY} : 0);
define('RECORD_FOR_LOG', isset(${RECORD_FOR_LOG_KEY}) ? pow(10, (int)${RECORD_FOR_LOG_KEY} > 0 ? (int)${RECORD_FOR_LOG_KEY} : 0) : 100000);
define('TIME_STEP_MIN', isset(${TIME_STEP_KEY}[MIN_KEY]) && (int)${TIME_STEP_KEY}[MIN_KEY] > 0 ? (int)${TIME_STEP_KEY}[MIN_KEY] : 0);
define('TIME_STEP_MAX', isset(${TIME_STEP_KEY}[MAX_KEY]) && (int)${TIME_STEP_KEY}[MAX_KEY] ? (int)${TIME_STEP_KEY}[MAX_KEY] : 0);

// validate 3 important parameters
if (0 == THREAD || 0 == RECORD_PER_THREAD || '' == PROJECT_NAME) {
    alertWindows(sprintf(MESSAGE_MUST_PASS_PARAMS, basename(__FILE__)), 1);
    die();
}

// notify by logging
firstValidFilePath(TOOLS_OUTPUT_DIRECTORY . DIRECTORY_SEPARATOR . LOG_DIRECTORY_NAME . DIRECTORY_SEPARATOR . $filename . LOG_EXTENSION, $logPath, true);
lig::setLogFilePath($logPath);
new lig(array(THREAD_KEY => THREAD, RECORD_PER_THREAD_KEY => RECORD_PER_THREAD, PROJECT_NAME_KEY => PROJECT_NAME));

// validate output
firstValidDirPath(TOOLS_OUTPUT_DIRECTORY . DIRECTORY_SEPARATOR . sprintf(GENERATE_DATA_DIRECTORY, PROJECT_NAME, RECORD_PER_THREAD), $resultDir);
$resultDir = getDirOutputPath($resultDir, sprintf(GENERATE_THREAD_DIRECTORY, THREAD));
ensureExistDir($resultDir);
echo sprintf(MESSAGE_PATH_OF_THREAD_DIRECTORY, $resultDir) . PHP_EOL;

// get master
$masterSamples = getMasters();

// validate & include functions
firstValidDirPath(CONFIG_SOURCE_PATH . DIRECTORY_SEPARATOR . 'function', $functionDir, true); # get function for field type function
scanPhpFile($functionDir);

// validate database information config
firstValidFilePath(CONFIG_SOURCE_PATH . DIRECTORY_SEPARATOR . "database" . CONFIG_EXTENSION, $databaseConfigPath);
firstValidFilePath(CONFIG_SOURCE_PATH . DIRECTORY_SEPARATOR . "database" . PHP_EXTENSION, $databasePhpPath);
$databasePath = $databasePhpPath;
if (empty($databasePath)) {
    echo sprintf(MESSAGE_NO_CONFIG_FOR, 'defining database') . PHP_EOL;
    exit;
}
$tableFieldInfo = require_once $databasePath;
echo sprintf(MESSAGE_PATH_OF_TABLE_FIELD_INFO_CONFIG, $databasePath) . PHP_EOL;

// backup config, database of the tool
$content = file_get_contents($toolConfigPath);
file_put_contents($resultDir . DIRECTORY_SEPARATOR . basename($toolConfigPath), $content);
$content = file_get_contents($databasePath);
file_put_contents($resultDir . DIRECTORY_SEPARATOR . basename($databasePath), $content);

// create records
generateSample();

