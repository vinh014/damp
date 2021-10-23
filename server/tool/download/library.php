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

/**
 * download items
 * @param string $downloadConfigPath for backup configuration
 * @param array $downloadConfig formatted information
 */
function download($downloadConfigPath, $downloadConfig = NULL)
{
    $commands = ':: copy and past to command line, not directly run' . PHP_EOL;
    $currentPathInfo = pathinfo(__FILE__);
    // exit if no exist information
    empty($downloadConfig) ? eval('return NULL;') : NULL;
    $storePath = $downloadConfig[STORE_PATH_KEY] ? $downloadConfig[STORE_PATH_KEY] : TOOLS_OUTPUT_DIRECTORY . DIRECTORY_SEPARATOR . DOWNLOAD_DIRECTORY_NAME;
    $batFileName = $downloadConfig[BAT_FILE_NAME_KEY] ? $downloadConfig[BAT_FILE_NAME_KEY] : DEFAULT_BAT_FILE_NAME;
    empty($storePath) || empty($batFileName) ? $incorrect = true : $incorrect = false;
    if ($incorrect) {
        alertWindows(MESSAGE_DOWNLOAD_CONFIGURATION_IS_INCORRECT, 2);
        exit(0);
    }
    ensureExistDir($storePath);
    // output directory
    $path = $storePath . DIRECTORY_SEPARATOR . date(DATE_TIME_FORMAT);
    $path = getDirOutputPath(dirname($path), basename($path));
    cleanDir($path);
    echo sprintf(MESSAGE_DOWNLOAD_DIRECTORY_IS, $path) . PHP_EOL;

    // support dir (bat)
    $batPath = $path . DIRECTORY_SEPARATOR . BAT_FOLDER_NAME;
    cleanDir($batPath);
    // data dir
    $dataPath = $path . DIRECTORY_SEPARATOR . CONTAINING_FOLDER_NAME;
    cleanDir($dataPath);
    // image path to mark successful
    $musicIcoPath = $currentPathInfo['dirname'] . DIRECTORY_SEPARATOR . MUSIC_ICON_FILE_NAME;
    // album name
    $downloadName = $downloadConfig[COLLECTION_KEY]; // get collection name
    correctFilename($downloadName); // correct name
    // real data dir
    $realDataPath = !empty($downloadName) ? $dataPath . DIRECTORY_SEPARATOR . $downloadName : $dataPath;
    // temp data dir
    $dataPath = !empty($downloadName) ? $dataPath . DIRECTORY_SEPARATOR . TEMP_CONTAINING_NAME : $dataPath;
    cleanDir($dataPath);
    // backup download config
    $data = file_get_contents($downloadConfigPath);
    file_put_contents($batPath . DIRECTORY_SEPARATOR . basename($downloadConfigPath), $data);
    $batFileName .= BAT_EXTENSION;
    correctFilename($batFileName);
    // list item for download
    $itemList = $downloadConfig[ITEM_LIST_KEY];
    $count = count($itemList);
    // max length of item index
    // ex. 100, 1000, 10000
    $group = strlen((string)$count);
    foreach ($itemList as $index => $item) {
        // remove whitespaces at begin & end
        array_walk($item, USER_TRIM_FUNCTION);
        // exit if url is invalid
        $url = $item[ITEM_URL_KEY];
        if (empty($url)) {
            continue;
        }
        $pathInfo = pathinfo($url);
        isset($item[ITEM_TYPE_KEY]) && $item[ITEM_TYPE_KEY] ? NULL : $item[ITEM_TYPE_KEY] = $pathInfo['extension'];
        isset($item[ITEM_NAME_KEY]) && $item[ITEM_NAME_KEY] ? NULL : $item[ITEM_NAME_KEY] = $pathInfo['filename'];
        $spIndex = sprintf('%0' . $group . 'd', $index + 1);
        $savedFileName = TEMP_FILE_NAME . $spIndex;
        $realFileName = $spIndex . ' - ' . $item[ITEM_NAME_KEY] . '.' . $item[ITEM_TYPE_KEY];
        correctFilename($realFileName);
        echo sprintf(MESSAGE_DOWNLOADING, $spIndex, $count, $url) . PHP_EOL;
        correctUrl($url);
        $savePath = $dataPath . DIRECTORY_SEPARATOR . $savedFileName;
        switch (true) {
            // buffer, faster for large size
            case 'curl' == $downloadConfig['readWay']:
                $targetHandle = fopen($savePath, 'wb');
                $errorHandle = fopen($savePath . '.err', 'w');
                $cURLHandle = curl_init($url);
                curl_setopt($cURLHandle, CURLOPT_TIMEOUT, $downloadConfig['timeout']);
                curl_setopt($cURLHandle, CURLOPT_FILE, $targetHandle);
                curl_setopt($cURLHandle, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($cURLHandle, CURLOPT_HEADER, 0);
                curl_setopt($cURLHandle, CURLOPT_BINARYTRANSFER, true);
                curl_setopt($cURLHandle, CURLOPT_VERBOSE, TRUE);
                curl_setopt($cURLHandle, CURLOPT_STDERR, $errorHandle);
                $result = curl_exec($cURLHandle);
                if (false === $result) {
                    echo sprintf(MESSAGE_CURL_ERROR, curl_error($cURLHandle)) . PHP_EOL;
                }
                curl_close($cURLHandle);
                fclose($targetHandle);
                fclose($errorHandle);
                break;
            // buffer, faster for large size
            case 'fopen' == $downloadConfig['readWay']:
                $sourceHandle = fopen($url, "rb");
                $targetHandle = fopen($savePath, 'wb');
                while (!feof($sourceHandle)) {
                    // if length is too large, source can't be download
                    $data = fread($sourceHandle, 1024);
                    fwrite($targetHandle, $data);
                }
                fclose($sourceHandle);
                fclose($targetHandle);
                break;
            // read once for small size
            case 'file' == $downloadConfig['readWay']:
                $data = file_get_contents($url);
                if (empty($data)) {
                    continue;
                }
                file_put_contents($savePath, $data);
                break;
            default:
                break;
        }
        // add command that rename tmp file
        $commands .= sprintf(COMMAND_RENAME, $savePath, $realFileName) . PHP_EOL;
    }

    if ($commands) {
        // rename directory
        $commands .= $dataPath != $realDataPath ? sprintf(COMMAND_MOVE, $dataPath, $realDataPath) . PHP_EOL : EMPTY_STRING;
        // notify successful by copy music icon
        $commands .= sprintf(COMMAND_COPY, $musicIcoPath, $path) . PHP_EOL;
        file_put_contents($batPath . DIRECTORY_SEPARATOR . $batFileName, $commands);
    }

    // finish
    echo sprintf(MESSAGE_DOWNLOAD_DIRECTORY_IS, $path) . PHP_EOL;
}

/**
 * list supported tag with decrease quality
 */
function getYoutubeItag()
{
    return array(
        38 =>
            array(
                ITAG_CODE => '38',
                ITAG_DESCRIPTION => 'MP4 HD (4K)',
                ITAG_EXTENSION => 'mp4',
            ),
        37 =>
            array(
                ITAG_CODE => '37',
                ITAG_DESCRIPTION => 'MP4 HD (1080p)',
                ITAG_EXTENSION => 'mp4',
            ),
        45 =>
            array(
                ITAG_CODE => '45',
                ITAG_DESCRIPTION => 'WebM HD (720p)',
                ITAG_EXTENSION => 'webm',
            ),
        22 =>
            array(
                ITAG_CODE => '22',
                ITAG_DESCRIPTION => 'MP4 HD (720p)',
                ITAG_EXTENSION => 'mp4',
            ),
        34 =>
            array(
                ITAG_CODE => '34',
                ITAG_DESCRIPTION => 'FLV (640p)',
                ITAG_EXTENSION => 'flv',
            ),
        44 =>
            array(
                ITAG_CODE => '44',
                ITAG_DESCRIPTION => 'WebM (480p)',
                ITAG_EXTENSION => 'webm',
            ),
        35 =>
            array(
                ITAG_CODE => '35',
                ITAG_DESCRIPTION => 'FLV (480p)',
                ITAG_EXTENSION => 'flv',
            ),
        43 =>
            array(
                ITAG_CODE => '43',
                ITAG_DESCRIPTION => 'WebM (360p)',
                ITAG_EXTENSION => 'webm',
            ),
        18 =>
            array(
                ITAG_CODE => '18',
                ITAG_DESCRIPTION => 'MP4 (360p)',
                ITAG_EXTENSION => 'mp4',
            ),
        5 =>
            array(
                ITAG_CODE => '5',
                ITAG_DESCRIPTION => 'FLV (240p)',
                ITAG_EXTENSION => 'flv',
            ),
    );
}

/** get download urls with different formats
 * @param $name
 * @param $urlMap
 * @param bool $onlyBest
 * @return array
 */
function getYoutubeDownloadConfig($name, $urlMap, $onlyBest = true)
{
    $result = array();
    $ytItags = getYoutubeItag();
    $urlMark = 'url=';
    // decode two times
    $urlMap = urldecode($urlMap);
    $urlMap = urldecode($urlMap);
    // get exist urls
    $urlMap = explode($urlMark, $urlMap);
    // remove whitespaces
    array_walk($urlMap, USER_TRIM_FUNCTION);
    foreach ($urlMap as $url) {
        if (empty($url)) {
            continue;
        }
        // get can-download url
        $tmp = explode(';', $url);
        $tmp = explode('&quality=', $tmp[0]);
        $url = $tmp[0];
        // get itag_code
        $itag = preg_replace('/^.*&itag=([0-9]*)&.*$/', "$1", $url);
        if (!isset ($ytItags[$itag])) {
            continue;
        }
        $result[$itag] = array(
            ITEM_URL_KEY => $url,
            ITEM_TYPE_KEY => $ytItags[$itag][ITAG_EXTENSION],
            ITEM_NAME_KEY => sprintf(YOUTUBE_ITEM_NAME, $name, $ytItags[$itag][ITAG_DESCRIPTION])
        );
    }
    // order by quality
    foreach ($ytItags as $itag => $value) {
        // if the format is not exist, unset it
        isset($result[$itag]) ? $ytItags[$itag] = $result[$itag] : eval('unset($ytItags[$itag]);');
    }
    $result = $ytItags;
    // get best format
    reset($result);
    $first = current($result);
    // return only best if require, default all
    return $onlyBest ? (isset($first) ? array($first) : array()) : $result;
}
 