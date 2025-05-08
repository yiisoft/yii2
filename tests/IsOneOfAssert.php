<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit;

use yii\helpers\VarDumper;

/**
 * IsOneOfAssert asserts that the value is one of the expected values.
 */
class IsOneOfAssert extends \PHPUnit\Framework\Constraint\Constraint
{
    /**
     * @var array the expected values
     */
    private $allowedValues = [];

    public function __construct(array $allowedValues)
    {
        $this->allowedValues = $allowedValues;
    }

    /**
     * Returns a string representation of the object.
     */
    public function toString(): string
    {
        $allowedValues = [];

        foreach ($this->allowedValues as $value) {
            $this->allowedValues[] = VarDumper::dumpAsString($value);
        }

        $expectedAsString = implode(', ', $allowedValues);

        return "is one of $expectedAsString";
    }

    protected function matches($other): bool
    {
        return in_array($other, $this->allowedValues, false);
    }
}
