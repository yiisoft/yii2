<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit;

/**
 * IsOneOfAssert asserts that the value is one of the expected values.
 */
class ResultPrinter extends \PHPUnit\TextUI\ResultPrinter
{
    public function __construct(
        $out = null,
        $verbose = false,
        $colors = \PHPUnit\TextUI\ResultPrinter::COLOR_DEFAULT,
        $debug = false,
        $numberOfColumns = 80,
        $reverse = false
    ) {
        if ($out === null) {
            $out = 'php://stdout';
        }

        parent::__construct($out, $verbose, $colors, $debug, $numberOfColumns, $reverse);
    }
}
