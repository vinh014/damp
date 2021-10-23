<?php
include 'C:/server/tool/log/index.php';

define('MYSQL_VERSION', preg_replace('/.*(\d\.\d+\.\d+).*/', '$1', exec('mysql -V')));

/**
 * in vhost.conf
 * SetEnv MIN_MYSQL_VERSION "5.1.0"
 * SetEnv MAX_MYSQL_VERSION "5.5.999"
 */

$minVersion = isset($_SERVER['MIN_MYSQL_VERSION']) ? $_SERVER['MIN_MYSQL_VERSION'] : null;
$maxVersion = isset($_SERVER['MAX_MYSQL_VERSION']) ? $_SERVER['MAX_MYSQL_VERSION'] : null;

if ($minVersion && version_compare($minVersion, MYSQL_VERSION, '>=')) {
    die('Minimum MySQL version is ' . $minVersion . '. Current is ' . MYSQL_VERSION . '.');
}
if ($maxVersion && version_compare($maxVersion, MYSQL_VERSION, '<=')) {
    die('Maximum MySQL version is ' . $maxVersion . '. Current is ' . MYSQL_VERSION . '.');
}

/**
 * in vhost.conf
 * SetEnv MIN_PHP_VERSION "5.3.0"
 * SetEnv MAX_PHP_VERSION "7.1.999"
 */

$minVersion = isset($_SERVER['MIN_PHP_VERSION']) ? $_SERVER['MIN_PHP_VERSION'] : null;
$maxVersion = isset($_SERVER['MAX_PHP_VERSION']) ? $_SERVER['MAX_PHP_VERSION'] : null;

if ($minVersion && version_compare($minVersion, PHP_VERSION, '>')) {
    die('Minimum PHP version is ' . $minVersion . '. Current is ' . PHP_VERSION . '.');
}
if ($maxVersion && version_compare($maxVersion, PHP_VERSION, '<')) {
    die('Maximum PHP version is ' . $maxVersion . '. Current is ' . PHP_VERSION . '.');
}