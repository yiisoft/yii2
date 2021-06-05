<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/* @var $this YiiRequirementChecker */
/* @var $summary array */
/* @var $requirements array[] */

echo "\nYii Application Requirement Checker\n\n";

echo "This script checks if your server configuration meets the requirements\n";
echo "for running Yii application.\n";
echo "It checks if the server is running the right version of PHP,\n";
echo "if appropriate PHP extensions have been loaded, and if php.ini file settings are correct.\n";

$header = 'Check conclusion:';
echo "\n{$header}\n";
echo str_pad('', strlen($header), '-') . "\n\n";

foreach ($requirements as $key => $requirement) {
    if ($requirement['condition']) {
        echo $requirement['name'] . ": OK\n";
    } else {
        echo $requirement['name'] . ': ' . ($requirement['mandatory'] ? 'FAILED!!!' : 'WARNING!!!') . "\n";
        echo 'Required by: ' . strip_tags($requirement['by']) . "\n";
        $memo = strip_tags($requirement['memo']);
        if (!empty($memo)) {
            echo 'Memo: ' . strip_tags($requirement['memo']) . "\n";
        }
    }
    echo "\n";
}

$summaryString = 'Errors: ' . $summary['errors'] . '   Warnings: ' . $summary['warnings'] . '   Total checks: ' . $summary['total'];
echo str_pad('', strlen($summaryString), '-') . "\n";
echo $summaryString;

echo "\n\n";
