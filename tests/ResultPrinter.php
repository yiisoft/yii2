<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit;

/**
 * Class ResultPrinter overrides \PHPUnit\TextUI\ResultPrinter constructor
 * to change default output to STDOUT and prevent some tests from fail when
 * they can not be executed after headers have been sent.
 */
class ResultPrinter extends \PHPUnit\TextUI\DefaultResultPrinter
{
    private $out = null;

    /**
     * @param null|resource|string $out
     * @param int|string $numberOfColumns
     */
    public function __construct(
        $out = null,
        $verbose = false,
        $colors = \PHPUnit\TextUI\DefaultResultPrinter::COLOR_DEFAULT,
        $debug = false,
        $numberOfColumns = 80,
        $reverse = false
    ) {
        if ($out === null) {
            $out = STDOUT;
        }

        parent::__construct($out, $verbose, $colors, $debug, $numberOfColumns, $reverse);
    }

    public function flush(): void
    {
        if ($this->out !== STDOUT) {
            parent::flush();
        }
    }
}
