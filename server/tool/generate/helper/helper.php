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
 * get csv file path
 */
function getCsvPath($tableName, $resultDir)
{
    $folderPath = $resultDir . "/";
    $filePath = $folderPath . $tableName;
    $i = 1;
    while (file_exists($filePath . '.csv')) {
        $filePath = $folderPath . $tableName . $i;
        $i++;
    }
    return $filePath . '.csv';
}

function createMysqlLoadDataFiles($tableName, $filePath, $row, $logPath = '', $resultDir)
{
    $fileName = preg_replace('/\.[^\.]+$/', '', basename($filePath));
    $listField = join(array_keys($row), ",");

    // Check function whether is exist and alert if not
    if (!function_exists('mysql_real_escape_string')) {
        echo PHP_EOL . sprintf(MESSAGE_FUNCTION_IS_UNDEFINED, 'mysql_real_escape_string') . PHP_EOL;
        exit(1);
    }
    // escape for sql query
    $escapeFilePath = mysql_real_escape_string($filePath);
    // LOCAL : If LOCAL is specified, the file is read by the client program on the client host and sent to the server
    // fix hard path because you can run sql from yourself machine
    $sql =
        "LOAD DATA LOCAL INFILE '{$escapeFilePath}' 
        INTO TABLE {$tableName} 
        FIELDS TERMINATED BY ',' ENCLOSED BY '\"' ESCAPED BY '\\\\' 
        LINES TERMINATED BY '\\n'  
        ({$listField});";

    $sqlFilePath = $resultDir . '/' . $fileName . '.sql';
    $file = fopen($sqlFilePath, 'w');
    fwrite($file, $sql . PHP_EOL);
    fclose($file);

    // sql truncate table
    $truncateSql = "TRUNCATE {$tableName};";
    $truncateFilePath = "{$resultDir}/{$fileName}_truncate.truncate_mysql";
    $truncateFile = fopen($truncateFilePath, 'w');
    fwrite($truncateFile, $truncateSql . PHP_EOL);
    fclose($truncateFile);
    $message = "data: {$filePath}," .
        PHP_EOL .
        "sql: {$sqlFilePath}" .
        PHP_EOL .
        "truncate: {$truncateFile}";
    lig::setLogFilePath($logPath);
    !empty($logPath) ? new lig($message) : NULL;
}


function createPostgresqlLoadDataFiles($tableName, $filePath, $row, $logPath = '', $resultDir)
{
    /**
     * @link http://www.postgresql.org/docs/8.3/static/sql-copy.html
     * COPY FROM can handle lines ending with newlines, carriage returns, or carriage return/newlines.
     * @link http://stackoverflow.com/questions/13770944/how-can-i-specify-the-schema-to-run-an-sql-file-against-in-the-postgresql-comman
     * set schema 'my_schema_01';
     */
    $fileName = preg_replace('/\.[^\.]+$/', '', basename($filePath));
    $listField = join(array_keys($row), ",");

    $sql = "
        COPY {$tableName} ({$listField}) FROM '{$filePath}'
	    WITH DELIMITER AS ',' CSV QUOTE  AS  '\"' ESCAPE AS '\\';";

    $sqlFilePath = $resultDir . '/' . $fileName . '.pgsql';
    $file = fopen($sqlFilePath, 'w');
    fwrite($file, $sql . PHP_EOL);
    fclose($file);

    // sql truncate table
    $truncateSql = "TRUNCATE {$tableName};";
    $truncateFilePath = "{$resultDir}/{$fileName}_truncate.truncate_pgsql";
    $truncateFile = fopen($truncateFilePath, 'w');
    fwrite($truncateFile, $truncateSql . PHP_EOL);
    fclose($truncateFile);
    $message = "data: {$filePath}," .
        PHP_EOL .
        "sql: {$sqlFilePath}" .
        PHP_EOL .
        "truncate: {$truncateFile}";
    lig::setLogFilePath($logPath);
    !empty($logPath) ? new lig($message) : NULL;
}

/**
 * desc create file control & file bat to inserting data into  database
 */
function createOracleLoadDataFiles($tableName, $filePath, $row, $logPath = '', $username, $password, $resultDir)
{
    $fileName = preg_replace('/\.[^\.]+$/', '', basename($filePath));
    $fileNameWithExtension = basename($filePath);
    $listField = join(array_keys($row), ",");

    // don't fix hard path because you must run bat file at oracle server
    // create control file
    $sql =
        "OPTIONS (ERRORS = -1, SKIP=0)
        LOAD DATA CHARACTERSET UTF8
        INFILE '{$fileNameWithExtension}' 
        APPEND
        INTO TABLE {$tableName} 
        FIELDS TERMINATED BY ','
        OPTIONALLY ENCLOSED BY '\"' 
        TRAILING NULLCOLS 
        ({$listField})";
    $controlFileName = $fileName . '.ctl';
    $controlFilePath = $resultDir . '/' . $controlFileName;
    $file = fopen($controlFilePath, 'w');
    fwrite($file, $sql . PHP_EOL);
    fclose($file);
    // create bat file
    $command = "sqlldr userid={$username}/{$password} control='{$controlFileName}'";
    $batFilePath = "{$resultDir}/{$fileName}.bat";
    $file = fopen($batFilePath, 'w');
    fwrite($file, $command . PHP_EOL);
    fclose($file);
    // sql truncate table
    $truncateSql = "TRUNCATE TABLE {$tableName};";
    $truncateFileName = $fileName . "_truncate.truncate_oracle";
    $truncateFilePath = $resultDir . '/' . $truncateFileName;
    $truncateFile = fopen($truncateFilePath, 'w');
    fwrite($truncateFile, $truncateSql . PHP_EOL);
    fclose($truncateFile);
    $message = "data: {$filePath}," .
        PHP_EOL .
        "control: {$controlFilePath}" .
        PHP_EOL .
        "bat: {$batFilePath}" .
        PHP_EOL .
        "truncate: {$truncateFilePath}";
    lig::setLogFilePath($logPath);
    !empty($logPath) ? new lig($message) : NULL;
}

/**
 * desc seed with microseconds
 */
function makeSeed()
{
    global ${ADDITIONAL_SEED_KEY};
    list($usec, $sec) = explode(' ', microtime());
    $ret = (float)$sec + ((float)$usec * 100000000) + ${ADDITIONAL_SEED_KEY};
    ${ADDITIONAL_SEED_KEY}++;
    return $ret;
}

/**
 * whether setting is null by rating
 */
function isSetNull($rateOfNull)
{
    srand(makeSeed());
    $randRate = rand(00, 99);
    srand();
    $randRate = $randRate / 100;
    if ($randRate < $rateOfNull) {
        return true;
    } else {
        return false;
    }
}

/**
 * desc rand with srand
 */
function randWithSrand($min, $max)
{
    if (isSetNull(RATE_OF_NULL)) {
        return null;
    } else {
        if ($max < $min) {
            $max = $min;
        }
        srand(makeSeed());
        $ret = rand($min, $max);
        srand();
        return $ret;
    }
}

/**
 * return random value of key or value
 */
function randomArray($array, $isKey, & $result, $multi = false, $limit = 0)
{
    if ($isKey) {
        if ($multi === true) {
            $limit = empty($limit) ? count($array) : $limit;
            $arrKey = arrayRandWithSrand($array, randWithSrand(1, $limit));
            if (!is_array($arrKey)) {
                $arrKey = array($arrKey);
            }
            $result = $arrKey;
        } else {
            $result = arrayRandWithSrand($array);
        }
    } else {
        if ($multi === true) {
            $limit = empty($limit) ? count($array) : $limit;
            $arrKey = arrayRandWithSrand($array, randWithSrand(1, $limit));
            if (!is_array($arrKey)) {
                $arrKey = array($arrKey);
            }
            $result = array();
            foreach ($arrKey as $key) {
                $result[] = $array[$key];
            }

        } else {
            if (is_array($array) && !empty($array)) {
                $result = $array[arrayRandWithSrand($array)];
            } else {
                $result = null;
            }
        }
    }
}

/**
 * array rand with srand
 */
function arrayRandWithSrand($array, $count = 1)
{
    if (isSetNull(RATE_OF_NULL)) {
        return null;
    } else {
        if (count($array) > 0) {
            if ($count > count($array)) {
                $count = count($array);
            }
            if ($count == 0) {
                return null;
            }
            srand(makeSeed());
            $ret = array_rand($array, $count);
            srand();
            return $ret;
        } else {
            return null;
        }
    }
}

/**
 * desc filter elements, which is these array (s), from containing array by conditons
 */
function filterArray($originalSamples, & $filteredSamples, $conditions)
{
    $filteredSamples = array();
    if (is_array($conditions) && !empty($conditions)) {
        foreach ($originalSamples as $sample) {
            $isSatisfied = true;
            foreach ($conditions as $condition) {
                if ($sample[$condition['key']] != $condition['value']) {
                    $isSatisfied = false;
                    break;
                }

            }
            if ($isSatisfied) {
                $filteredSamples[] = $sample;
            }
        }
    } else {
        $filteredSamples = $originalSamples;
    }
}

/**
 * add time
 */
function addTime($date, & $newDate, $year = 0, $month = 0, $day = 0, $hour = 0, $minute = 0, $second = 0)
{
    $additional =
        ' ' . $year . ' year' .
        ' ' . $month . ' month' .
        ' ' . $day . ' day' .
        ' ' . $hour . ' hour' .
        ' ' . $minute . ' minute' .
        ' ' . $second . ' second';
    $newTime = strtotime($additional, strtotime($date));
    // set date
    $newDate = date('Y-m-d H:i:s', $newTime);
}


