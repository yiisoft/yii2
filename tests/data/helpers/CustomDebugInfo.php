<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\helpers;

/**
 * CustomDebugInfo serves for the testing of `__debugInfo()` PHP magic method.
 *
 * @see \yiiunit\framework\helpers\VarDumperTest
 */
class CustomDebugInfo
{
    public $volume;
    public $unitPrice;

    /**
     * @see https://www.php.net/manual/en/language.oop5.magic.php#language.oop5.magic.debuginfo
     *
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'volume' => $this->volume,
            'totalPrice' => $this->volume * $this->unitPrice,
        ];
    }
}
