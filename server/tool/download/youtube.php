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

scanPhpFile(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'constant');
scanPhpFile(__FILE__, $excepts = 'download.php');
// get config path
firstValidFilePath(CONFIG_SOURCE_PATH . DIRECTORY_SEPARATOR . basename(__FILE__, PHP_EXTENSION) . CONFIG_EXTENSION, $youtubeConfigPath);
// convert to array
file2config($youtubeConfigPath, $youtubeConfigs);
// convert into input format of download function
$ytDownloadConfig = array();
foreach ($youtubeConfigs[ITEM_LIST_KEY] as $index => $ytconfig) {
    // whether only get best
    isset($ytconfig[YOUTUBE_ONLY_BEST_KEY]) ? $onlyBest = (bool)$ytconfig[YOUTUBE_ONLY_BEST_KEY] : $onlyBest = true;
    // get download urls for current video
    $rs = getYoutubeDownloadConfig($ytconfig[YOUTUBE_NAME_KEY], $ytconfig[YOUTUBE_MAP_KEY], $onlyBest);
    $ytDownloadConfig = array_merge($ytDownloadConfig, $rs);
}
$youtubeConfigs[ITEM_LIST_KEY] = $ytDownloadConfig;
download($youtubeConfigPath, $youtubeConfigs);

 