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
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'core.php';

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'constant.php';

$beforeClasses = get_declared_classes();
scanPhpFile(__FILE__);

$nlog = lig::nlog(); # get value of nlog

$phpCode = ''; # store php code that create other versions of log
$sampleT = file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'logtruncate.php'); # sample logt
$sampleDie = file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'logdie.php'); # sample logdie
$sampleTDie = file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'logtruncatedie.php'); # sample logtdie

# remove php open tag
$sampleT = str_replace('<' . '?php', '', $sampleT);
$sampleDie = str_replace('<' . '?php', '', $sampleDie);
$sampleTDie = str_replace('<' . '?php', '', $sampleTDie);

# remove require_once
$sampleT = preg_replace('/require_once.*$/m', '', $sampleT);
$sampleDie = preg_replace('/require_once.*$/m', '', $sampleDie);
$sampleTDie = preg_replace('/require_once.*$/m', '', $sampleTDie);

# create log{i}t
for ($i = 1; $i <= $nlog; $i++) {
    $phpCode .= str_replace('logtruncate', 'log' . $i . 'truncate', $sampleT);
}
# create log{i}tdie
for ($i = 1; $i <= $nlog; $i++) {
    $phpCode .= str_replace('logtruncate', "log{$i}truncate", $sampleTDie);
}

# create log{i}
for ($i = 1; $i <= $nlog; $i++) {
    $tmp = str_replace('logdie extends lig', 'log' . $i . 'die extends lig', $sampleDie);
    $tmp = str_replace('die();', '', $tmp); # remove die();
    $phpCode .= str_replace('die', '', $tmp); # remove die in class name
}

# create log{i}die
for ($i = 1; $i <= $nlog; $i++) {
    $tmp = str_replace('logdie extends lig', "log{$i}die extends log{$i}", $sampleDie);
    $phpCode .= str_replace("'lig.php'", "'log{$i}.php'", $tmp);
}

eval(' ' . $phpCode . ' ');

// build lognx logny
$sample = file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'logx.php');
$sample .= file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'logy.php');
$sample = str_replace('<' . '?php', '', $sample);
$sample = preg_replace('/require_once.*$/m', '', $sample);
$phpCode = '';
for ($i = 1; $i <= lig::$nlog; $i++) {
    $tmp = str_replace('logx', 'log' . $i . 'x', $sample);
    $phpCode .= str_replace('logy', 'log' . $i . 'y', $tmp);
}
eval(' ' . $phpCode . ' ');
unset($phpCode);
// build lognpx lognpy
$sample = file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'logxprint.php');
$sample .= file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'logyprint.php');
$sample .= file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'logyprintdie.php');
$sample = str_replace('<' . '?php', '', $sample);
$sample = preg_replace('/require_once.*$/m', '', $sample);
$phpCode = '';
for ($i = 1; $i <= lig::$nlog; $i++) {
    $tmp = str_replace('logxprint', 'log' . $i . 'xprint', $sample);
    $phpCode .= str_replace('logyprint', 'log' . $i . 'yprint', $tmp);
}
eval(' ' . $phpCode . ' ');
unset($phpCode);

# init php code to execute
$phpCode = '';
# get sample
$sampleR = file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'logrequest.php');
# remove php open tag
$sampleR = str_replace('<' . '?php', '', $sampleR);
# remove require_once
$sampleR = preg_replace('/require_once.*$/m', '', $sampleR);
# create log{i}r
for ($i = 1; $i <= $nlog; $i++) {
    $phpCode .= str_replace('logrequest', 'log' . $i . 'request', $sampleR);
}
# execute code
eval(' ' . $phpCode . ' ');
# unset code
unset($phpCode);

# init php code to execute
$phpCode = '';
# get sample
$sampleC = file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'logcall.php');
# remove php open tag
$sampleC = str_replace('<' . '?php', '', $sampleC);
# remove require_once
$sampleC = preg_replace('/require_once.*$/m', '', $sampleC);
# create log{i}c
for ($i = 1; $i <= $nlog; $i++) {
    $phpCode .= str_replace('logcall', 'log' . $i . 'call', $sampleC);
}
# execute code
eval(' ' . $phpCode . ' ');
# unset code
unset($phpCode);

$afterClasses = get_declared_classes();
$functionSample = 
<<<'EOD'
function newxyz() {
    $expressions = func_get_args();
    $expressionNames = array();
    foreach ($expressions as $index => $arg) {
        $expressionNames[] = '$expressions[' . $index . ']';
    }
    eval('new xyz(' . implode(',', $expressionNames) . ');');
}
EOD;
$phpCode = '';
foreach(array_diff($afterClasses, $beforeClasses) as $class) {
    $phpCode .= str_replace('xyz', $class, $functionSample);
}
# execute code
eval(' ' . $phpCode . ' ');
# unset code
unset($phpCode);
unset($afterClasses, $beforeClasses);