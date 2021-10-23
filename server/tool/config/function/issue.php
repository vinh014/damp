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

class Project1_Issue
{
    /**
     * create issue code
     * @param $params
     * @return string
     */
    public static function createIssueCode2($params)
    {
        return self::createIssueCode($params['count']);
    }

    /**
     * create issue code include issue type, time, index
     * @param $count index count
     * @return string issue code
     */
    private static function createIssueCode($count)
    {
        $types = array(
            '0' => 'A',
            '1' => 'B',
            '2' => 'C',
            '3' => 'D',
            '4' => 'E',
            '5' => 'F',
            '6' => 'G',
            '7' => 'H',
            '8' => 'I',
            '9' => 'J',
        );
        // get issue type
        $type = $types[randWithSrand(0, 9)];
        // get time
        $step = TIME_STEP_MAX - TIME_STEP_MIN + 1;
        $min = TIME_STEP_MIN + floor($count / RECORD_PER_STEP) * $step;
        $max = TIME_STEP_MAX + floor($count / RECORD_PER_STEP) * $step;
        $time = date('ym', strtotime('-' . randWithSrand($min, $max) . ' month'));
        // get index
        $sequenceNo = sprintf('%05d', $count - floor($count / RECORD_PER_STEP) * RECORD_PER_STEP);
        // issue code
        return $type . $time . $sequenceNo;
    }

    /**
     * create datetime base issue code
     * @param $params
     * @return string
     */
    public static function createDatetimeFromIssueCode($params)
    {
        $createdDate = '';
        $value = '';
        $row = $params['row'];
        $fieldInfo = $params['fieldInfo'];
        $year = isset($fieldInfo['datetime']['year']) ? (int)$fieldInfo['datetime']['year'] : 0;
        $month = isset($fieldInfo['datetime']['month']) ? (int)$fieldInfo['datetime']['month'] : 0;
        $day = isset($fieldInfo['datetime']['day']) ? (int)$fieldInfo['datetime']['day'] : 0;
        $hour = isset($fieldInfo['datetime']['hour']) ? (int)$fieldInfo['datetime']['hour'] : 0;
        $minute = isset($fieldInfo['datetime']['minute']) ? (int)$fieldInfo['datetime']['minute'] : 0;
        $second = isset($fieldInfo['datetime']['second']) ? (int)$fieldInfo['datetime']['second'] : 0;
        self::getDatetimeInIssueCode($row[$fieldInfo['code']], $createdDate);
        addTime($createdDate, $value, $year, $month, $day, $hour, $minute, $second);
        return $value;
    }

    /**
     * extract datetime from issue code
     * format issue code: [TYPE][TIME][SEQUENCE_NUMBER]
     */
    public static function getDatetimeInIssueCode($code, & $result)
    {
        // random
        $hour = randWithSrand(0, 23);
        $minute = randWithSrand(0, 59);
        $second = randWithSrand(0, 59);
        $day = randWithSrand(1, 28);
        // get year month
        preg_match('/^[a-zA-Z]+/', $code, $matches);
        $issueTypeLength = strlen($matches[0]);
        $year = substr($code, $issueTypeLength, 2);
        $month = substr($code, $issueTypeLength + 2, 2);
        $result = date(DATE_TIME_FORMAT2, mktime($hour, $minute, $second, $month, $day, $year));
    }

    /**
     * create date base issue code
     * @param $params
     * @return bool|string
     */
    public static function createDateFromIssueCode($params)
    {
        $createdDate = '';
        $value = '';
        $row = $params['row'];
        $fieldInfo = $params['fieldInfo'];
        $year = isset($fieldInfo['datetime']['year']) ? (int)$fieldInfo['datetime']['year'] : 0;
        $month = isset($fieldInfo['datetime']['month']) ? (int)$fieldInfo['datetime']['month'] : 0;
        $day = isset($fieldInfo['datetime']['day']) ? (int)$fieldInfo['datetime']['day'] : 0;
        $hour = isset($fieldInfo['datetime']['hour']) ? (int)$fieldInfo['datetime']['hour'] : 0;
        $minute = isset($fieldInfo['datetime']['minute']) ? (int)$fieldInfo['datetime']['minute'] : 0;
        $second = isset($fieldInfo['datetime']['second']) ? (int)$fieldInfo['datetime']['second'] : 0;
        self::getDatetimeInIssueCode($row[$fieldInfo['code']], $createdDate);
        addTime($createdDate, $value, $year, $month, $day, $hour, $minute, $second);

        return date('Y-m-d', strtotime($value));
    }

    /**
     * create time base issue code
     * @param $params
     * @return bool|string
     */
    public static function createTimeFromIssueCode($params)
    {
        $createdDate = '';
        $value = '';
        $row = $params['row'];
        $fieldInfo = $params['fieldInfo'];
        $year = isset($fieldInfo['datetime']['year']) ? (int)$fieldInfo['datetime']['year'] : 0;
        $month = isset($fieldInfo['datetime']['month']) ? (int)$fieldInfo['datetime']['month'] : 0;
        $day = isset($fieldInfo['datetime']['day']) ? (int)$fieldInfo['datetime']['day'] : 0;
        $hour = isset($fieldInfo['datetime']['hour']) ? (int)$fieldInfo['datetime']['hour'] : 0;
        $minute = isset($fieldInfo['datetime']['minute']) ? (int)$fieldInfo['datetime']['minute'] : 0;
        $second = isset($fieldInfo['datetime']['second']) ? (int)$fieldInfo['datetime']['second'] : 0;
        self::getDatetimeInIssueCode($row[$fieldInfo['code']], $createdDate);
        addTime($createdDate, $value, $year, $month, $day, $hour, $minute, $second);
        return date('H:i:s', strtotime($value));
    }
}
