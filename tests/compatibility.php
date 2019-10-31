<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/*
 * Ensures compatibility with PHPUnit < 6.x
 */

namespace PHPUnit\Framework\Constraint {
    if (!class_exists('PHPUnit\Framework\Constraint\Constraint') && class_exists('PHPUnit_Framework_Constraint')) {
        abstract class Constraint extends \PHPUnit_Framework_Constraint
        {
        }
    }
}

namespace PHPUnit\TextUI {
    if (!class_exists('\PHPUnit\TextUI\ResultPrinter') && class_exists('PHPUnit_TextUI_ResultPrinter')) {
        class ResultPrinter extends \PHPUnit_TextUI_ResultPrinter
        {
        }
    }
}

namespace PHPUnit\Framework\Error {
    if (!class_exists('PHPUnit\Framework\Error\Notice') && class_exists('PHPUnit_Framework_Error_Notice')) {
        class Notice extends \PHPUnit_Framework_Error_Notice
        {
        }
    }
}
