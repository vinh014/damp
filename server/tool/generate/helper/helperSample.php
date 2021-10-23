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
 * create sample
 * return true if creating sample successful, otherwise false
 */
function createSample($tableName, $tableInfo, & $row, & $count, $allTableRow, $curSampleIndex)
{
    global $masterSamples, $baseCount, $maxCount;
    $row = $conditions = array();
    is_array($tableInfo['field']) ? NULL : $tableInfo['field'] = array();

    // current table refer to tempTable
    // reset stored index after creating record of current table
    // apply only current record (on all field)
    $storedRecordIndexes = array();

    // browser each field detail
    foreach ($tableInfo['field'] as $fieldInfo) {
        isset($fieldInfo['subtype']) && !!$fieldInfo['subtype'] ? null : $fieldInfo['subtype'] = '';
        $year = isset($fieldInfo['datetime']['year']) ? (int)$fieldInfo['datetime']['year'] : 0;
        $month = isset($fieldInfo['datetime']['month']) ? (int)$fieldInfo['datetime']['month'] : 0;
        $day = isset($fieldInfo['datetime']['day']) ? (int)$fieldInfo['datetime']['day'] : 0;
        $hour = isset($fieldInfo['datetime']['hour']) ? (int)$fieldInfo['datetime']['hour'] : 0;
        $minute = isset($fieldInfo['datetime']['minute']) ? (int)$fieldInfo['datetime']['minute'] : 0;
        $second = isset($fieldInfo['datetime']['second']) ? (int)$fieldInfo['datetime']['second'] : 0;

        switch ($fieldInfo['type']) {
            // index of row
            case 'serial':
                $count++;
                break;
            case 'id':
                $row[$fieldInfo['name']] = $count;
                break;
            // recursive id or parent id i.e. child refer to parent
            case 'rid':
                $row[$fieldInfo['name']] = randWithSrand($baseCount[$tableName], $maxCount[$tableName]);
                break;
            // set value that is return by function
            case 'function':
                $tmpValue = '';
                $params = array(
                    'count' => $count,
                    'row' => $row,
                    'fieldInfo' => $fieldInfo,

                );
                eval('$tmpValue = ' . $fieldInfo['function'] . '($params);');
                $row[$fieldInfo['name']] = $tmpValue;
                break;
            // bit 0 or 1
            case 'flag':
                $row[$fieldInfo['name']] = randWithSrand(0, 99) <= (int)($fieldInfo['rateTrue'] * 100) ? '1' : '0';
                break;
            case 'datetime':
                $value = '';
                switch ($fieldInfo['subtype']) {
                    # from now
                    case 'now':
                        $value = date('Y-m-d H:i:s'); // get now time
                        break;
                    # is created by exist datetime field with additional time
                    case 'base':
                        addTime($row[$fieldInfo['base']], $value, $year, $month, $day, $hour, $minute, $second);
                        break;
                    default:
                        break;
                }
                $row[$fieldInfo['name']] = $value;
                break;
            case 'date':
                $value = '';
                // base other datetime field
                if (isset($fieldInfo['belong']) && !empty($row[$fieldInfo['belong']])) {
                    $value = $row[$fieldInfo['belong']];
                }
                $row[$fieldInfo['name']] = date('Y-m-d', strtotime($value));
                break;
            case 'time':
                $value = '';
                // base other datetime field
                if (!empty($row[$fieldInfo['belong']])) {
                    $value = $row[$fieldInfo['belong']];
                }
                $row[$fieldInfo['name']] = date('H:i:s', strtotime($value));
                break;

            case 'master':
                $samples = array();
                $receivedSamples = array();
                # validate whether get multi samples
                $fieldInfo['multi'] = isset($fieldInfo['multi']) ? (bool)$fieldInfo['multi'] : false;
                # separator among values
                $fieldInfo['glue'] = isset($fieldInfo['glue']) ? $fieldInfo['glue'] : '|';
                # maximum of total values
                $fieldInfo['limit'] = isset($fieldInfo['limit']) ? (int)$fieldInfo['limit'] : 0;

                $conditions = array();
                isset($fieldInfo['condition']) && is_array($fieldInfo['condition']) ? NULL : $fieldInfo['condition'] = array();

                # convert into usage conditions
                foreach ($fieldInfo['condition'] as $index => $condition) {
                    switch (true) {
                        case isset($condition['belong']) && 0 < strlen($condition['belong']):
                            $conditions[] = array(
                                'key' => $condition['index'],
                                'value' => $row[$condition['belong']]
                            );
                            break;
                        case isset($condition['value']) && 0 < strlen($condition['value']):
                            $conditions[] = array(
                                'key' => $condition['index'],
                                'value' => $condition['value']
                            );
                            break;
                        default:
                            break;
                    }
                }
                filterArray($masterSamples[$fieldInfo['master']], $samples, $conditions); # filter by condition
                randomArray($samples, $isKey = false, $receivedSamples, $fieldInfo['multi'], $fieldInfo['limit']); # get values
                // convert single value into 'multi values' format
                !$fieldInfo['multi'] && $receivedSamples ? $receivedSamples = array($receivedSamples) : null;

                # for each field in fieldList, fill data
                foreach ($fieldInfo['fieldList'] as $realField => $indexes) {
                    $indexes = (array)$indexes;
                    $row[$realField] = '';
                    $values = array();
                    foreach ($receivedSamples as $info) {
                        $singleFieldValue = '';
                        // merge indexes into one
                        foreach ($indexes as $masterIndex) {
                            $singleFieldValue .= $info[$masterIndex];
                        }
                        # build value of current info
                        $values[] = $singleFieldValue;
                    }
                    // join multi values into one that set to field
                    $row[$realField] = trim(join($fieldInfo['glue'], $values));
                }
                break;

            // count is part of email
            case 'count_email':
                $row[$fieldInfo['name']] = 'email' . $count . '@live.com';
                break;

            case 'copy':
                $value = '';
                foreach ($fieldInfo['fieldList'] as $field) {
                    $value .= $row[$field];
                }
                $row[$fieldInfo['name']] = $value;
                break;

            # number or string number
            case 'number':
                $value = '';
                $min = NULL;
                $max = NULL;
                $length = 1;
                isset($fieldInfo['min']) ? $min = (int)$fieldInfo['min'] : null;
                isset($fieldInfo['max']) ? $max = (int)$fieldInfo['max'] : null;
                isset($fieldInfo['length']) ? $length = (int)$fieldInfo['length'] : null;
                while ((int)$value == 0) {
                    $value = '';
                    // random length
                    $realLength = randWithSrand(1, $length);
                    // fill 0-9
                    for ($i = 1; $i <= $realLength; $i++) {
                        $value .= randWithSrand(0, 9);
                    }
                    # $value must be greater than minimum
                    null !== $min && $min > (int)$value ? $value = 0 : null;
                    # $value must be less than maximum
                    null !== $max && $max < (int)$value ? $value = 0 : null;
                }
                # format with need length
                $value = sprintf('%0' . $length . 'd', $value);

                # format value into need type: int, float, string, ...
                if (!empty($fieldInfo['realType'])) {
                    $phpCode = '$value = (' . $fieldInfo['realType'] . ')$value;';
                    eval($phpCode);
                }
                $row[$fieldInfo['name']] = $value;
                break;

            # delete field
            case 'unset':
                unset($row[$fieldInfo['name']]);
                break;

            /**
             * foreign key (type 1)
             * keep referenced index through all fields that is same refer to a table
             * only create once & reuse; reset it at creating sample
             */
            case 'reference':
            case 'reference1':
                $referencedTable = $fieldInfo['reference']['table']; # referenced table
                $referencedField = $fieldInfo['reference']['field']; # referenced field

                $countRecord = count($allTableRow[$referencedTable]); # total sample of referenced table
                !$countRecord ? $countRecord = 1 : null;

                if (isset($storedRecordIndexes[$tableName][$referencedTable])) {
                    $copiedIndex = $storedRecordIndexes[$tableName][$referencedTable];
                } else {
                    $copiedIndex = randWithSrand(0, $countRecord - 1);
                    $storedRecordIndexes[$tableName][$referencedTable] = $copiedIndex; # store referenced index
                }

                # get referenced sample
                $value = NULL;
                if (isset($allTableRow[$referencedTable][$copiedIndex])) {
                    $value = $allTableRow[$referencedTable][$copiedIndex][$referencedField];
                }

                $row[$fieldInfo['name']] = $value;
                break;

            /**
             * foreign key (type 2)
             */
            case 'reference2':
                $referencedTable = $fieldInfo['reference']['table'];

                $countRecord = count($allTableRow[$referencedTable]); # total sample of referenced table
                !$countRecord ? $countRecord = 1 : null;

                $copiedIndex = randWithSrand(0, $countRecord - 1);
                foreach ($fieldInfo['reference']['fieldList'] as $realField => $target) {
                    $row[$realField] = $allTableRow[$referencedTable][$copiedIndex][$target];
                }
                break;

            # constant
            case 'constant':
                $row[$fieldInfo['name']] = $fieldInfo['value'];
                break;

            # order of samples: 1 -> multi of current table
            case 'multi':
                $row[$fieldInfo['name']] = $curSampleIndex + 1;

            default:
                break;
        }
    }
    // Dynamic Master with limit totalRecord
    if ($tableInfo['totalRecord'] && $tableInfo['isMaster']) {
        $masterSamples[$tableName][] = array_values($row);
    }
    return true;
}

/**
 * desc generate sample data
 */
function generateSample()
{
    global $tableFieldInfo, $globalTotalRecord, $masterSamples, $logPath, $resultDir, $baseCount, $maxCount;

    // first row
    $minRow = RECORD_PER_THREAD * (THREAD - 1) + 1;
    // final row
    $maxRow = RECORD_PER_THREAD * THREAD;
    $filePath = array();
    $file = array();
    $row = array();
    $logRecordCount = 0;
    $baseCount = array();

    // init value for each table
    foreach ($tableFieldInfo as $tableName => $tableInfo) {

        // only active table is continue in creating record
        $tableInfo['active'] = !empty($tableInfo['active']) ? (bool)$tableInfo['active'] : false;
        if (!$tableInfo['active']) {
            continue;
        }
        // output csv file
        $filePath[$tableName] = uniqueFilePath($resultDir, $tableName, 'csv');
        $file[$tableName] = fopen($filePath[$tableName], 'a');
        $max = isset($tableInfo['multi']) ? (int)$tableInfo['multi'] : 0;
        $max = !empty($max) ? $max : 1;
        $baseCount[$tableName] = RECORD_PER_THREAD * $max * (THREAD - 1);
        $maxCount[$tableName] = RECORD_PER_THREAD * $max * (THREAD);
        $globalTotalRecord[$tableName] = 0;
    }
    # count of each table is starting at base count
    $count = $baseCount;

    // create sample
    for ($i = $minRow; $i <= $maxRow; $i++) {
        // reset records for all tables
        $allTableRow = array();
        // create sample for each table
        foreach ($tableFieldInfo as $tableName => $tableInfo) {

            // only active table is continue in creating record
            $tableInfo['active'] = $tableInfo['active'] ? (bool)$tableInfo['active'] : false;
            if (!$tableInfo['active']) {
                continue;
            }

            // if true, multi is right by in config, other is rand value
            $tableInfo['fixMulti'] = isset($tableInfo['fixMulti']) ? (bool)$tableInfo['fixMulti'] : false;
            $max = isset($tableInfo['multi']) ? (int)$tableInfo['multi'] : 0;
            $max = $max ? $max : 1;
            $max = $tableInfo['fixMulti'] ? $max : randWithSrand(1, $max);
            // verify master, total record
            empty($tableInfo['master']) ? $tableInfo['master'] = $tableName : '';
            !isset($tableInfo['totalRecord']) ? $tableInfo['totalRecord'] = 0 : '';
            'master' === $tableInfo['totalRecord'] ? $tableInfo['totalRecord'] = count($masterSamples[$tableInfo['master']]) : '';
            if ((int)$tableInfo['totalRecord'] > 0 && $globalTotalRecord[$tableName] >= (int)$tableInfo['totalRecord']) {
                continue; // move to another table
                // if totalRecord beyond the limit, no record of current table is created
                // [WARNING] there has errors if referenced table is limited record
                // ex, referenced table totalRecord 50, referencing table totalRecord 100,
                // so, record 51 -> 100 can't follow to 'referenced table', its record is empty
            }
            // population of table
            for ($j = 1; $j <= $max; $j++) {
                if ((int)$tableInfo['totalRecord'] > 0 && $globalTotalRecord[$tableName] >= (int)$tableInfo['totalRecord']) {
                    break; // don't create record of this table
                    // [REFERENCE] as above
                }
                $globalTotalRecord[$tableName]++;
                $row[$tableName] = array();
                $ret = createSample($tableName, $tableInfo, $row[$tableName], $count[$tableName], $allTableRow, $j - 1);

                $allTableRow[$tableName][$j - 1] = NULL;
                if ($ret) {
                    fputcsv($file[$tableName], $row[$tableName]);
                    $allTableRow[$tableName][$j - 1] = $row[$tableName];
                }
            }
        }
        // notify by logging
        if ($logRecordCount % RECORD_FOR_LOG == 0 || $i == $maxRow) {

            // write data to real file on hard disk to free memory
            // at dynamic master, generated record is added back into master data
            foreach ($tableFieldInfo as $tableName => $tableInfo) {

                // only active table is continue in creating record
                $tableInfo['active'] = !empty($tableInfo['active']) ? (bool)$tableInfo['active'] : false;
                if (!$tableInfo['active']) {
                    continue;
                }

                fclose($file[$tableName]);
                $file[$tableName] = fopen($filePath[$tableName], 'a');

            }

            $message = "Row : {$logRecordCount} at " . date('Y-m-d H:i:s');
            lig::setLogFilePath($logPath);
            new lig($message);
            echo $message . PHP_EOL;
        }
        // count for logging
        $logRecordCount++;
    }

    // create loader
    foreach ($tableFieldInfo as $tableName => $tableInfo) {
        // only active table is continue in creating record
        $tableInfo['active'] = !empty($tableInfo['active']) ? (bool)$tableInfo['active'] : false;
        if (!$tableInfo['active']) {
            continue;
        }
        fclose($file[$tableName]);
        $tableInfo['databaseType'] = isset($tableInfo['databaseType']) && $tableInfo['databaseType'] ? $tableInfo['databaseType'] : 'mysql';
        // creating script for importing data into database
        switch ($tableInfo['databaseType']) {
            case 'postgresql':
                createPostgresqlLoadDataFiles(
                    $tableName,
                    $filePath[$tableName],
                    $row[$tableName],
                    $logPath,
                    $resultDir
                );
                break;
            case 'mysql':
                createMysqlLoadDataFiles(
                    $tableName,
                    $filePath[$tableName],
                    $row[$tableName],
                    $logPath,
                    $resultDir
                );
                break;
            case 'oracle':
                $datetimeFields = array();
                // list datetime field
                foreach ($tableInfo['field'] as $index => $fieldInfo) {
                    switch ($fieldInfo['type']) {
                        case 'datetime':
                            $datetimeFields[] = $fieldInfo['name'];
                            break;
                        default:
                            break;
                    }
                }
                // format datetime file
                foreach ($row[$tableName] as $field => $value) {
                    if (in_array($field, $datetimeFields)) {
                        $part = "\"TO_DATE(:{$field},'YYYY-MM-DD HH24:MI:SS')\"";
                        $row[$tableName]["{$field} {$part}"] = $value;
                        unset($row[$tableName][$field]);
                    }
                }
                createOracleLoadDataFiles(
                    $tableName,
                    $filePath[$tableName],
                    $row[$tableName],
                    $logPath,
                    ORACLE_USERNAME,
                    ORACLE_PASSWORD,
                    $resultDir
                );
                break;
            default:
                break;
        }
    }
    // group bat
    $groupBatFilePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'group.group_bat';
    $tmpArray = explode('.', basename($groupBatFilePath));
    $outputPath = uniqueFilePath($resultDir, $tmpArray[0], $tmpArray[1]);
    copy($groupBatFilePath, $outputPath);

    // notify finish
    $message = sprintf(MESSAGE_FINISH_CREATE_PROJECT, PROJECT_NAME, THREAD, RECORD_PER_THREAD, date(DATE_TIME_FORMAT2));
    lig::setLogFilePath($logPath);
    lig::showLogPath(true);
    new lig(PHP_EOL . $message);
}

 