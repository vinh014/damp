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
 * that last in first out
 */
class Stack
{
    // list used items
    public static $files = array();
    public static $classes = array();
    public static $functions = array();

    /**
     * store calling orders of called function by store the information of called method
     * @param string $_file used file name
     * @param string $_class used class name
     * @param string $_function used function name
     */
    public static function pushLog($_file = '', $_class = '', $_function = '')
    {
        $className = __CLASS__;
        $_file = basename($_file);
        $_file = trim($_file);
        eval ($className . '::$files[] = $_file;');
        $_class = trim($_class);
        eval ($className . '::$classes[] = $_class;');
        $_function = trim($_function);
        eval ($className . '::$functions[] = $_function;');
    }

    /**
     * get files stack
     */
    public static function getFiles()
    {
        $className = __CLASS__;
        $ret = NULL;
        eval ('$ret = ' . $className . '::$files;');
        return $ret;
    }

    /**
     * get classes stack
     */
    public static function getClasses()
    {
        $className = __CLASS__;
        $ret = NULL;
        eval ('$ret = ' . $className . '::$classes;');
        return $ret;
    }

    /**
     * get functions stack
     */
    public static function getFunctions()
    {
        $className = __CLASS__;
        $ret = NULL;
        eval ('$ret = ' . $className . '::$functions;');
        return $ret;
    }

    /**
     * get files, classes, functions stacks
     */
    public static function getLogStack()
    {
        $className = __CLASS__;
        $ret = NULL;
        eval (
            '
                $ret = array(
                    "files" => ' . $className . '::getFiles(),
                        "classes" => ' . $className . '::getClasses(),
                        "functions" => ' . $className . '::getFunctions()
                    );
                '
        );
        return $ret;
    }

    /**
     *  get first class that be called
     */
    public static function getFirstClass()
    {
        $filtered = self::filterConstruct();
        reset($filtered['classes']);
        return current($filtered['classes']);
    }

    /**
     * get files, classes, functions that belong to function '__construct'
     */
    public static function filterConstruct()
    {
        $className = __CLASS__;
        $functions = array();
        $files = array();
        $classes = array();

        # restore stacks into local variables
        eval ('$files = ' . $className . '::$files;');
        eval ('$classes = ' . $className . '::$classes;');
        eval ('$functions = ' . $className . '::$functions;');
        if (version_compare(phpversion(), '7.2.0', '<')) {
            # create filter
            $callback = create_function('$function', 'return $function == "__construct";');
            # filter functions stack
            $functions = array_filter($functions, $callback);
        } else {
            # filter functions stack
            $functions = array_filter($functions, function($function) {
                return $function == "__construct";
            });
        }
        # get files corresponding to functions by index
        $files = array_intersect_key($files, $functions);
        # get classes corresponding to functions by index
        $classes = array_intersect_key($classes, $functions);
        return array(
            'files' => $files,
            'classes' => $classes,
            'functions' => $functions,
        );
    }

    /**
     * @desc reset all properties to initial value
     */
    public static function reset()
    {
        # get class attributes
        $className = __CLASS__;
        $classAttributes = get_class_vars($className);
        $staticAttributes = $classAttributes;
        # turn off reporting
        $level = error_reporting();
        error_reporting(0);
        # reset attributes
        foreach ($staticAttributes as $name => $value) {
            eval($className . '::$' . $name . ' = array();');
        }
        # restore reporting
        error_reporting($level);

    }
}
 