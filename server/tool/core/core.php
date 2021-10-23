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
# enable all warning & error
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', '1');

/**
 * scan php files in landmark & auto include if needed
 * @param string $landmark directory or file
 * @param string , array $excepts item name that is not care
 * @param boolean $recursive whether scan subdirectory
 * @param boolean $including whether including valid items
 * @return array php files is included
 */
function scanPhpFile($landmark = EMPTY_STRING, $excepts = EMPTY_STRING, $recursive = false, $including = true)
{
    $validPhpFiles = array();
    # if landmark is not exist, stop by return empty array
    if (!file_exists($landmark)) {
        return array();
    }
    $isFile = is_file($landmark) ? true : false;
    # directory landmark base landmark
    $landmarkDir = $isFile ? dirname($landmark) : $landmark;
    # validate except
    is_string($excepts) ? $excepts = explode(DELIMITER, $excepts) : null;
    foreach (scandir($landmarkDir) as $index => $item) {
        switch (true) {
            # virtual directory (. & ..) is skipped
            case $item == VIRTUAL_DIRECTORY_CURRENT:
            case $item == VIRTUAL_DIRECTORY_PARENT:
                break;
            # if is subdirectory, scan it
            case is_dir($landmarkDir . DIRECTORY_SEPARATOR . $item) && $recursive:
                $subFiles = scanPhpFile(
                    $landmarkDir . DIRECTORY_SEPARATOR . $item,
                    $excepts,
                    $recursive,
                    $including
                );
                $validPhpFiles = array_merge($validPhpFiles, $subFiles);
                break;
            # current file can't be again included
            # item is not php file
            # ignore item if it is excepted
            case $item == basename($isFile ? $landmark : EMPTY_STRING):
            case $item == basename($item, PHP_EXTENSION):
            case in_array($item, $excepts):
                break;
            # it is valid php files, include it if needed
            default:
                $path = $landmarkDir . DIRECTORY_SEPARATOR . $item;
                $validPhpFiles[] = $path;
                $including ? require_once $path : null;
                break;
        }
    }

    return $validPhpFiles;
}

/**
 * correct file name
 * @param string $filename
 * @param string $replacement
 * @link en.wikipedia.org/wiki/Filename
 */
function correctFilename(&$filename, $replacement = DEFAULT_REPLACEMENT)
{
    # replace invalid characters by default valid characters for replacement
    $replacement = preg_replace(INVALID_CHARACTER_IN_FILE_NAME, DEFAULT_REPLACEMENT, $replacement);
    # replace invalid characters by replacement for file name
    $filename = preg_replace(INVALID_CHARACTER_IN_FILE_NAME, $replacement, $filename);
    $filename = trim($filename);
    # max length of file name of windows is 255
    $filename = mb_substr($filename, 0, 255, 'UTF-8');
}

/**
 * correct Uri ex. replace character space by '%20'
 * @param string $url uri
 */
function correctUrl(&$url)
{
    $url = str_replace(' ', '%20', $url);
}

/**
 * convert underscored name to CamelCased name
 * @param string $underscoredString
 * @return string
 */
function underscored2camelCased($underscoredString)
{
    # Step 1: replace underscore by space
    $underscoredString = str_replace(CHARACTER_UNDERSCORE, CHARACTER_SPACE, $underscoredString);
    # Step 2: uppercase first character
    $underscoredString = ucwords($underscoredString);
    # Step 3: remove space
    $camelCasedString = str_replace(CHARACTER_SPACE, EMPTY_STRING, $underscoredString);
    return $camelCasedString;
}

/**
 * whether is config line
 * @param string $line
 * @param string $separate
 * @return return array if it is config line, other return false
 */
function isConfigLine($line, $separate = CONFIG_SEPARATOR)
{
    $line = trim($line);
    switch (true) {
        case empty($line): # is empty line
        case strpos($line, MARK_OF_COMMENT_LINE) === 0: # is comment line
        case count($parts = explode($separate, $line)) <= 1: # don't contain $separate
            return FALSE; # exit
            break;
        default:
            break;
    }
    $count = count($parts);
    foreach ($parts as $index => $part) {
        $parts[$index] = trim($parts[$index]);
        # if empty key (not value) is exist, exit
        if ($parts[$index] === EMPTY_STRING && $index < $count - 1) {
            return FALSE;
        }
    }
    return $parts;
}

/**
 * convert config line into array
 * @param string $configPath
 * @param array $configs
 */
function file2config($configPath, &$configs)
{
    $configs = array();
    $lines = array();
    ${USE_ABOVE_KEY} = NULL;
    ${CREATE_NEW_AT_LAST_VAR} = NULL;
    if (is_file($configPath)) {
        $lines = file($configPath, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
    }
    is_array($lines) ? null : $lines = array();
    // remove whitespaces
    array_walk($lines, USER_TRIM_FUNCTION);
    foreach ($lines as $line) {
        # split into keys, value
        $parts = isConfigLine($line, CONFIG_SEPARATOR);
        if (empty($parts)) {
            continue;
        }
        # execute line as php code, reset at each line
        $exePHP = false;
        if (EXE_AS_PHP_KEY == $parts[0]) {
            $exePHP = true;
            unset($parts[0]);
            # reindex values
            $parts = array_values($parts);
        }
        $startPoint = isset($configs[START_POINT_KEY]) ? $configs[START_POINT_KEY] : NULL;
        if (!empty($startPoint)) {
            $startPointKeys = array();
            # get newest start point
            browseKeyValue($startPoint, $startPointKeys, array(USE_NEWEST => true));
            # create variable start_point
            !empty($startPointKeys) ? ${START_POINT_KEY} = implode(CONFIG_SEPARATOR, $startPointKeys) : NULL;
            # push start point at begin of current parts
            # startPoint is invalid for command startPoint and command variable
            if (START_POINT_KEY != $parts[0] && VARIABLE_KEY != $parts[0]) {
                $parts = array_merge($startPointKeys, $parts);
            }
        }

        # push data to variable ex. {variable}
        foreach ($parts as $index => $string) {
            # split `..{string}..` into `string}..`
            $opens = explode(OPEN_BRACKET, $string);
            if (count($opens) > 1) {
                foreach ($opens as $ondex => $open) {
                    # split `string}..` => `string` or variable
                    $closes = explode(CLOSE_BRACKET, $open);
                    if (count($closes) > 1) {
                        # replace by value
                        $closes[0] = ${$closes[0]};
                        # composite parts
                        $opens[$ondex] = implode(EMPTY_STRING, $closes);
                    }
                }
                # composite parts
                $parts[$index] = implode(EMPTY_STRING, $opens);
            }
        }

        # ensure, in start point key, any no empty value is key
        if (!!$parts && START_POINT_KEY == $parts[0] && !!$parts[count($parts) - 1]) {
            $parts[] = EMPTY_STRING;
        }
        $newParts = array();
        $stop_here = false;
        $count = count($parts);

        ## "\x7C" ~ '|'
        # PHP|variable|ab|"11\x7C22"
        # c|{ab}|value ~ c|11|C22|value
        for ($i = 0; $i < $count; $i++) {
            $string = trim($parts[$i]);

            # empty key is exist
            if ($i < $count - 1 && !$string) {
                $stop_here = true;
                break;
            }

            $subConfig = isConfigLine($string, CONFIG_SEPARATOR);
            !!$subConfig ? $newParts = array_merge($newParts, $subConfig) : $newParts[] = $string;
        }

        if (!!$stop_here) {
            continue; # to new line
        }
        $parts = $newParts;

        $i = 0;
        $count = count($parts);
        # fore dynamic assigning
        $ref = &$configs;
        while ($i < $count) {
            switch (true) {
                # current element is value
                case $i == $count - 1:
                    if ($exePHP) {
                        eval('$tmp = ' . $parts[$i] . ';');
                    } else {
                        $tmp = $parts[$i];
                    }
                    $ref = $tmp;
                    break;
                # empty key is exist
                case $parts[$i] === EMPTY_STRING:
                    # stop browsing
                    $i = $count;
                    break;
                # is valid key that point to scalar value (int, string, ..)
                case $i == $count - 2:
                    switch (true) {
                        # append mode, not use above key
                        case $parts[$i] == APPEND_KEY && !${USE_ABOVE_KEY}:
                            # append mode, use above key, create new at last
                        case $parts[$i] == APPEND_KEY && !!${USE_ABOVE_KEY} && !!${CREATE_NEW_AT_LAST_VAR}:
                            $ref[] = NULL;

                        # append mode, use above key, turn off create new at last
                        case $parts[$i] == APPEND_KEY:
                            # end key mode
                        case $parts[$i] == TO_END_KEY:
                            # empty array is not allowed
                            !count($ref) ? $ref[] = NULL : NULL;
                            end($ref);
                            $curKey = key($ref);
                            break;


                        default : # is valid key that point to value
                            # ensure the key is exist
                            isset($ref[$parts[$i]]) ? NULL : $ref[$parts[$i]] = NULL;
                            $curKey = $parts[$i];
                            break;

                    }

                    $tmp = $ref[$curKey];
                    unset($ref[$curKey]);
                    # update current key to latest
                    $ref[$curKey] = $tmp;
                    # re point to current key
                    $ref = &$ref[$curKey];
                    break;
                # is valid key that point to compound value (array)
                case $i <= $count - 3:
                    switch (true) {
                        # append mode, not use above key
                        case $parts[$i] == APPEND_KEY && !${USE_ABOVE_KEY}:
                            $ref[] = array();

                        # append mode
                        case $parts[$i] == APPEND_KEY:
                            # end key mode
                        case $parts[$i] == TO_END_KEY:
                            # empty array is not allowed
                            !count($ref) ? $ref[] = array() : NULL;
                            end($ref); # move to end
                            $curKey = key($ref);
                            # ensure type of value is array
                            is_array($ref[$curKey]) ? NULL : $ref[$curKey] = array();
                            break;
                        # is valid key that point to value
                        default:
                            # ensure type of value is array
                            isset($ref[$parts[$i]]) && is_array($ref[$parts[$i]]) ? NULL : $ref[$parts[$i]] = array();
                            $curKey = $parts[$i];
                            break;
                    }

                    $tmp = $ref[$curKey];
                    unset($ref[$curKey]);
                    # update current key to latest
                    $ref[$curKey] = $tmp;
                    # re point to current key
                    $ref = &$ref[$curKey];
                    break;
                default:
                    break;
            }
            # next part
            $i++;
        }

        # command create variable
        if (VARIABLE_KEY == $parts[0]) {
            # move to latest variable
            end($configs[VARIABLE_KEY]);
            # get variable name
            $key = key($configs[VARIABLE_KEY]);
            # get value of variable
            $value = current($configs[VARIABLE_KEY]);
            $tmp = array();
            # get all latest keys, value
            browseKeyValue($value, $tmp, array(USE_NEWEST => true));
            # create variable with restore value
            ${$key} = implode(CONFIG_SEPARATOR, $tmp);
            # remove keys & value for just created variable
            unset($configs[VARIABLE_KEY]);
        }
        if (UNSET_KEY == $parts[0]) {
            # remove key & corresponding value
            unset(${$configs[UNSET_KEY]});
        }
    }
    # remove start point before exit function
    unset($configs[START_POINT_KEY]);
}

/**
 * try to get firstly valid file path, try to creating file by path if it's not exist
 * @param array or string $paths candidate paths
 * @param string $validPath firstly file path
 * @param boolean $tryCreate try to creating file if it's not exist
 */
function firstValidFilePath($paths, &$validPath, $tryCreate = false)
{
    $level = error_reporting();
    # turn off reporting
    error_reporting(0);

    $paths = (array)$paths;
    $validPath = EMPTY_STRING;

    foreach ($paths as $path) {
        switch (true) {
            case is_file($path):
            case $tryCreate && FALSE !== file_put_contents($path, EMPTY_STRING):
                $validPath = $path;
                # break switch and outer foreach
                break 2;
            default:
                break;
        }
    }
    # restore reporting
    error_reporting($level);
}

/**
 * try to get firstly valid dir path, try to creating dir by path if it's not exist
 * @param array or string $paths candidate paths
 * @param string $validPath firstly dir path
 * @param boolean $tryCreate try to creating dir if it's not exist
 */
function firstValidDirPath($paths, &$validPath, $tryCreate = true)
{
    $paths = (array)$paths;
    # turn off reporting
    $level = error_reporting();
    error_reporting(0);

    $validPath = EMPTY_STRING;
    foreach ($paths as $index => $path) {
        switch (true) {
            case is_dir($path):
            case $tryCreate && mkdir($path, 0777, true):
                $validPath = $path;
                break 2; # break switch and outer foreach
            default:
                break;
        }
    }
    # restore reporting
    error_reporting($level);
}

/**
 * generate unique file path
 * @param string $folderContainer container directory path
 * @param string $filename
 * @param string $fileType
 * @return unique file path that can be ended with .1, .2, .3 ...
 */
function uniqueFilePath($folderContainer, $filename, $fileType)
{
    $filePath = $folderContainer . DIRECTORY_SEPARATOR . $filename;
    $i = 1;
    while (file_exists($filePath . $fileType) && is_file($filePath . $fileType)) {
        # append by index
        $filePath = $folderContainer . DIRECTORY_SEPARATOR . $filename . "." . $i;
        $i++;
    }
    return $filePath . '.' . $fileType;
}

/**
 * generate unique dir path
 * @param string $folderContainer container directory path
 * @param string $dirName
 * @return unique dir path that can be ended with .1, .2, .3 ...
 */
function getDirOutputPath($folderContainer, $dirName)
{
    $folderPath = $folderContainer . DIRECTORY_SEPARATOR . $dirName;
    $i = 1;
    while (file_exists($folderPath) && is_dir($folderPath)) {
        $folderPath = $folderContainer . DIRECTORY_SEPARATOR . $dirName . '.' . $i;
        $i++;
    }

    return $folderPath;
}

/**
 * remove directory recursively
 * @param string $dir dir path for deleting
 * @param boolean $recursive
 * @note remove directory and items in it
 * @link php.net/manual/en/function.rmdir.php
 */
function removeDir($dir, $recursive = true)
{
    # ensure item for deleting, is directory
    if (!is_dir($dir)) {
        unlink($dir);
        return;
    }
    $objects = scandir($dir);
    foreach ($objects as $object) {
        # virtual directory can't be processed
        if ('.' === $object || '..' === $object) {
            continue;
        }

        if ('dir' == filetype($dir . DIRECTORY_SEPARATOR . $object)) {
            # recursively remove subdirectory
            $recursive ? removeDir($dir . DIRECTORY_SEPARATOR . $object, $recursive) : null;
        } else {
            # remove file
            unlink($dir . DIRECTORY_SEPARATOR . $object);
        }
    }
    reset($objects);
    # remove current directory
    rmdir($dir);
}

/**
 * try to creating directory
 * @param $path directory to be created
 */
function ensureExistDir($path)
{
    # directory is exist
    if (file_exists($path) && is_dir($path)) {
        return;
    } else {
        # try to creating directory if it's not exist
        mkdir($path, 0777, true);
    }
}

/**
 * clean all items in the directory
 * @param $path directory path
 */
function cleanDir($path)
{
    ensureExistDir($path);
    removeDir($path, true);
    ensureExistDir($path);
}

/**
 * write content to file with encoding UTF-8
 * @param string $path
 * @param string $content
 */
function writeFile($path, $content = EMPTY_STRING)
{
    $fp = fopen($path, 'w');
    if ($fp && $content) {
        # encode in UTF-8 - Add byte order mark
        fwrite($fp, pack("CCC", 0xef, 0xbb, 0xbf));
        fwrite($fp, $content);
    }
    fclose($fp);
}

/**
 * alert windows
 * @param string $message
 * @param int $time in seconds
 */
function alertWindows($message = EMPTY_STRING, $time = 0)
{
    $time = (int)$time;
    exec('msg * /time:' . $time . ' "' . $message . '"');
}

/**
 * get keys, value by browsing in depth
 * @param array|string $array keys, value
 * @param array $result
 * @param array configs additional condition
 */
function browseKeyValue($array, &$result, $configs = array())
{
    $data = array();
    # whether only latest keys, value
    ${USE_NEWEST} = false;
    if (isset($configs[USE_NEWEST])) {
        ${USE_NEWEST} = (bool)$configs[USE_NEWEST];
    }
    # convert into readable format
    is_array($array) ? NULL : $array = array($array => NULL);
    # default is get all keys, value
    $data = $array;
    if (${USE_NEWEST}) {
        end($array);
        # rebuild data with only latest key, value
        $data = array(
            key($array) => current($array)
        );
    }
    foreach ($data as $key => $value) {
        # stored key into result
        0 < strlen($key) ? $result[] = $key : NULL;
        if (is_array($value)) {
            # recursively browse
            browseKeyValue($value, $result, $configs);
        } elseif (0 < strlen($value)) {
            $result[] = $value;
        }
    }
}

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'constant' . DIRECTORY_SEPARATOR . 'logic.php';
scanPhpFile(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'constant');

if (!defined('CONFIG_SOURCE_PATH')) {
    echo sprintf(MESSAGE_NO_DIRECTORY_CONFIG) . PHP_EOL;
    exit;
}
scanPhpFile(__FILE__);
# create function user_trim for array_walk
eval(USER_TRIM_FUNCTION_CODE);
$filename = basename(__FILE__, PHP_EXTENSION);
firstValidFilePath(CONFIG_SOURCE_PATH . DIRECTORY_SEPARATOR . $filename . CONFIG_EXTENSION, $coreConfigPath);
if (empty($coreConfigPath)) {
    echo sprintf(MESSAGE_NO_CONFIG_FOR, 'root') . PHP_EOL;
    exit;
}
file2config($coreConfigPath, $coreConfigs);
if ($coreConfigs['TOOLS_OUTPUT_DIRECTORY']) {
    define('TOOLS_OUTPUT_DIRECTORY', $coreConfigs['TOOLS_OUTPUT_DIRECTORY']);
} else {
    define('TOOLS_OUTPUT_DIRECTORY', DRIVE_C);
}
ensureExistDir(TOOLS_OUTPUT_DIRECTORY);