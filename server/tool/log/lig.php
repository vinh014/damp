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
 * a alternative for var_dump, a stand-alone library
 * all calling, all request a file
 * @author vinhnv@live.com
 */
class lig
{
    # property is prefixed PREFIX_NOT_RESET that isn't reset to NULL
    public static $___order = 1; # order of calling
    public static $___uniqid = NULL; # uniqid string
    public static $separate = NULL; # between values that is logged
    public static $logFilePath = NULL; # log filepath
    public static $logFileName = NULL; # log filename
    public static $logDir = NULL; # log dir support for logFileName
    public static $logExtension = NULL; # .html, .htm, .txt, ... default is .log
    public static $hasDatetime = NULL; # whether log datetime information
    public static $showLogPath = NULL; # whether echo log filepath
    public static $noLog = NULL; # no output any information by any way, for creating object sample
    public static $isEcho = NULL; # priority DESC: noLog > echo > write file
    public static $mode = NULL; # mode for write log file ex. a, w, ...
    public static $nlog = NULL; # [log logt logdie logtdie]...[logn lognt logndie logntdie]
    public static $___customPrefix = false; // use custom prefix argument
    public static $___configs = NULL;
    public static $___sample = NULL;
    public static $___logTime = array(); # in milliseconds with key is filename
    public $author = NULL;

    /**
     * @desc reset properties of class & object
     * @param $object reset object
     * @param $actionMode get only properties of class or of object or all
     */
    public static function reset(&$object = NULL, $actionMode = NULL)
    {
        Stack::reset();

        $allAttributes = array();
        $___sample = NULL;
        $className = __CLASS__;

        $classAttributes = get_class_vars($className);
        eval($className . '::setVar($object, NULL, $allAttributes);'); # get all attributes of class

        extract($allAttributes, EXTR_OVERWRITE); # create local variable

        $objectAttributes = get_object_vars(!!$object ? $object : $___sample); # get attributes of object of object

        !$objectAttributes ? $objectAttributes = array() : NULL;

        $staticAttributes = $classAttributes;
        foreach ($objectAttributes as $key => $value) {
            unset($staticAttributes[$key]); # split static & object attributes
        }

        switch (true) {
            case NULL === $actionMode:
                # do nothing
                break;
            case 'static' === $actionMode: # only static attributes
                $objectAttributes = array();
                break;
            case 'object' === $actionMode: # only object attributes
                $staticAttributes = array();
                break;
            default:
                break;
        }

        foreach ($staticAttributes as $name => $value) {
            # only reset properties that don't prefix by PREFIX_NOT_RESET
            if (0 !== strpos($name, PREFIX_NOT_RESET)) {
                eval($className . '::$' . $name . ' = NULL;');
            }
        }
        foreach ($objectAttributes as $name => $value) {
            # only reset properties that don't prefix by PREFIX_NOT_RESET
            if (0 !== strpos($name, PREFIX_NOT_RESET)) {
                eval('$object->' . $name . ' = NULL;');
            }
        }
    }

    /**
     * @desc create variable from attributes of class and object
     * @param &$object
     * @param $actionMode get only properties of class or of object or all
     * @param array $allAttributes
     */
    public static function setVar($object = NULL, $actionMode = NULL, &$allAttributes)
    {
        $className = __CLASS__;
        $allAttributes = array();
        $___sample = NULL;
        $classAttributes = get_class_vars($className);
        eval('$___sample = ' . $className . '::$___sample;');

        $objectAttributes = get_object_vars(!!$object ? $object : $___sample);
        !$objectAttributes ? $objectAttributes = array() : NULL;

        $staticAttributes = $classAttributes;
        foreach ($objectAttributes as $key => $value) {
            unset($staticAttributes[$key]);
        }
        switch (true) {
            case NULL === $actionMode:
                # do nothing
                break;
            case 'static' === $actionMode: # only static attributes
                $objectAttributes = array();
                break;
            case 'object' === $actionMode: # only object attributes
                $staticAttributes = array();
                break;
            default:
                break;
        }

        foreach ($staticAttributes as $name => $value) {
            eval('$allAttributes["' . $name . '"] = ' . $className . '::$' . $name . ';');
        }
        foreach ($objectAttributes as $name => $value) {
            eval('$allAttributes["' . $name . '"] = $object->' . $name . ';');
        }
    }

    /**
     * @param array $params
     */
    public static function writeLogs(array $params)
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);
        $logData = $logFilePath = $hasDatetime = $showLogPath = $mode = $logFileName = $logDir = $logExtension = null;
        extract($params);
        $className = Stack::getFirstClass();
        $className ? NULL : $className = __CLASS__;
        $filename = $logFileName ? $logFileName : basename($className, 'die');
        correctFilename($filename);
        $eol = PHP_EOL;
        $datetime = date(DATE_TIME_FORMAT2);
        $modes = array('r', 'r+', 'w', 'w+', 'a', 'a+', 'x', 'x+', 'c', 'c+'); # supported mode of writing log file
        $defaultMode = 'a'; # default mode
        $logData = $eol . $logData;
        if ($hasDatetime) {
            $logTime = round(microtime(true) * 1000);
            if (!isset(self::$___logTime[$filename])) {
                $exeTime = 'N/A';
            } else {
                $exeTime = $logTime - self::$___logTime[$filename];
            }

            $logData = $eol . ' - [at ' . $datetime . ' or ' . $logTime . ' ms and in ' . $exeTime . ' ms] - ' . $logData;
            self::$___logTime[$filename] = $logTime;
        }

        $logExtension ? '' : $logExtension = LOG_EXTENSION;

        $defaultPath = TOOLS_OUTPUT_DIRECTORY . DIRECTORY_SEPARATOR .
            LOG_DIRECTORY_NAME . DIRECTORY_SEPARATOR .
            ($logDir ? $logDir . DIRECTORY_SEPARATOR : '') .
            $filename . $logExtension;

        $pathList = array(
            $logFilePath,
            $defaultPath
        );
        in_array($mode, $modes) ? NULL : $mode = $defaultMode;
        foreach ($pathList as $path) {
            if (!$path) {
                continue;
            }

            $isFile = is_file($path);
            !is_dir(dirname($path)) ? mkdir(dirname($path), 0777, true) : NULL;
            if (255 < strlen($path)) {
                $deletedLength = strlen($path) - 255;
                $info = pathinfo($path);
                $info['filename'] = substr($info['filename'], 0, strlen($info['filename']) - $deletedLength);
                $path = $info['dirname'] . '/' . $info['filename'] . '.' . $info['extension'];
                unset($info);
            }
            $filePointer = fopen($path, $mode);
            if ($filePointer) {
                echo $showLogPath ? PHP_EOL . sprintf(MESSAGE_PATH_OF_LOG_FILE, $path) : '';
                # try to creating utf8 file if it's not exist
                !$isFile ? fwrite($filePointer, pack("CCC", 0xef, 0xbb, 0xbf)) : NULL;
                fwrite($filePointer, $logData); # write message
                fclose($filePointer);
                break;
            }
        }
    }

    public static function setSeparate($_separate = PHP_EOL)
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);
        $className = __CLASS__;
        $name = 'separate';
        eval($className . '::$' . $name . ' = $_' . $name . ';');
    }

    public static function setLogFilePath($_logFilePath = '')
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);
        $className = __CLASS__;
        $name = 'logFilePath';
        eval($className . '::$' . $name . ' = $_' . $name . ';');
    }

    public static function setExt($_logExtension = '')
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);
        self::setLogExtension($_logExtension);
    }

    public static function setLogExtension($_logExtension = '')
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);
        $className = __CLASS__;
        $name = 'logExtension';
        eval($className . '::$' . $name . ' = $_' . $name . ';');
    }

    public static function logDatetime($_hasDatetime = false)
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);
        $className = __CLASS__;
        $name = 'hasDatetime';
        eval($className . '::$' . $name . ' = $_' . $name . ';');
    }

    public static function showLogPath($_showLogPath = false)
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);
        $className = __CLASS__;
        $name = 'showLogPath';
        eval($className . '::$' . $name . ' = $_' . $name . ';');
    }

    public static function onlyEcho($_isEcho = false)
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);
        $className = __CLASS__;
        $name = 'isEcho';
        eval($className . '::$' . $name . ' = $_' . $name . ';');
    }

    public static function noLog($_noLog = false)
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);
        $className = __CLASS__;
        $name = 'noLog';
        eval($className . '::$' . $name . ' = $_' . $name . ';');
    }

    public static function setMode($_mode = 'a')
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);
        $className = __CLASS__;
        $name = 'mode';
        eval($className . '::$' . $name . ' = $_' . $name . ';');
    }

    public static function setConfig($____configs = NULL)
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);
        $className = __CLASS__;
        $name = '___configs';
        eval($className . '::$' . $name . ' = $_' . $name . ';');
    }

    public static function setNlog($_nlog = NULL)
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);
        $className = __CLASS__;
        $name = 'nlog';
        eval($className . '::$' . $name . ' = $_' . $name . ';');
    }

    public static function getUniqid()
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);
        $className = __CLASS__;
        $name = '___uniqid';
        $____uniqid = NULL;
        eval('$_' . $name . ' = ' . $className . '::$' . $name . ';');
        return $____uniqid;
    }

    public static function nlog()
    {
        $nlog = 1;
        self::init($tmp, 'static');
        $className = __CLASS__;
        eval('$nlog = (int)' . $className . '::$nlog;');
        return $nlog ? $nlog : 1;
    }

    /**
     * @desc init all properties of class and object from config
     * @param $object
     * @param string $actionMode get only properties of class or of object or all
     */
    public static function init(&$object, $actionMode = NULL)
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);
        $className = __CLASS__;
        $filenameExt = basename(__FILE__);
        $filename = basename(__FILE__, PHP_EXTENSION);
        $staticAttributes = array();
        $objectAttributes = array();

        firstValidFilePath(CONFIG_SOURCE_PATH . DIRECTORY_SEPARATOR . 'log' . CONFIG_EXTENSION, $logConfigPath);

        if (empty($logConfigPath)) {
            echo sprintf(MESSAGE_NO_CONFIG_FOR, __CLASS__) . PHP_EOL;
            exit;
        }
        file2config($logConfigPath, $config);

        switch (true) {
            case NULL === $actionMode:
                is_array($config['static']) ? $staticAttributes = $config['static'] : NULL;
                is_array($config['object']) ? $objectAttributes = $config['object'] : NULL;
                break;
            case 'static' === $actionMode:
                is_array($config['static']) ? $staticAttributes = $config['static'] : NULL;
                break;
            case 'object' === $actionMode:
                is_array($config['object']) ? $objectAttributes = $config['object'] : NULL;
                break;
            default:
                break;
        }

        foreach ($staticAttributes as $name => $value) {
            eval('$null_' . $name . ' = is_null(' . $className . '::$' . $name . ');'); # whether property is null
            $continue = false;

            eval('if(!$null_' . $name . ') {$continue = true;};'); # if property isn't NULL, skip this property

            if ($continue) {
                continue;
            }
            eval($className . '::$' . $name . ' = $value;');
        }

        foreach ($objectAttributes as $name => $value) {
            eval('$null_' . $name . ' = is_null($object->' . $name . ');'); # whether property is null
            $continue = false;

            eval('if(!$null_' . $name . ') {$continue = true;}'); # if property isn't NULL, skip this property
            if ($continue) {
                continue;
            }

            eval('$object->' . $name . ' = $value;');
        }
    }

    public function setAuthor($_author = '')
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);
        $name = 'author';
        eval('$this->' . $name . ' = $_' . $name . ';');
    }

    public function __call($name, $arguments)
    {
        self::setLogDir('__call');
        self::setLogFileName($name);
        $this->__construct($arguments);
    }

    public static function setLogDir($_logDir = '')
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);
        $className = __CLASS__;
        $name = 'logDir';
        eval($className . '::$' . $name . ' = $_' . $name . ';');
    }

    public static function setLogFileName($_logFileName = '')
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);
        $className = __CLASS__;
        $name = 'logFileName';
        eval($className . '::$' . $name . ' = $_' . $name . ';');
    }

    /**
     * @desc for constructing also logging values
     */
    public function __construct()
    {
        Stack::pushLog(__FILE__, __CLASS__, __FUNCTION__);

        $logData = array();
        $allAttributes = NULL;
        $separate = NULL;
        $noLog = NULL;
        $isEcho = NULL;
        $totalArgument = func_num_args();
        $className = get_class($this);

        eval($className . '::init($this);'); # init all properties of class and object
        eval($className . '::setVar($this, NULL, $allAttributes);'); # get all properties of class, object
        extract($allAttributes, EXTR_OVERWRITE); # create local variables

        if ($totalArgument > 0) {
            $expressions = func_get_args();
            for ($i = 0; $i < $totalArgument; $i++) {
                # handle error 'Nesting level too deep - recursive dependency' > var_export is failure
                # handle circular references > var_export is failure
                # handle simultaneously many expression > var_export, print_r is failure
                # can return result without using output buffer > var_dump is failure
                # auto limit depth of variable for dumping > var_export, print_r is failure
                $logData[] = print_r($expressions[$i], true);
            }
        } else {
            $logData[] = 'logging by class ' . $className;
        }
        $logData = implode($separate, $logData);

        switch (true) {
            case $noLog == true: # no log no echo no write
                break;
            case $isEcho == true: # echo
                echo '<pre>' . $logData . '</pre>';
                break;
            default: # write log file
                eval($className . '::writeLogs(array(
                            "logData" => $logData,
                            "logFilePath" => $logFilePath,
                            "hasDatetime" => $hasDatetime,
                            "showLogPath" => $showLogPath,
                            "mode" => $mode,
                            "logFileName" => $logFileName,
                            "logDir" => $logDir,
                            "logExtension" => $logExtension,
                ));');
                break;
        }
        eval($className . '::reset($this);'); # reset all properties of class & object after using
    }

}

lig::noLog(true); # no log
lig::$___sample = new lig(); # init sample for getting object properties
lig::$___uniqid = uniqid('_', true); # creating unique string that using in logr

