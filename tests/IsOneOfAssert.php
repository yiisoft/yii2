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
    /** @psalm-param string[] $allowedValues */
    public function __construct(private array $allowedValues)
    {
    }

    /**
     * Returns a string representation of the object.
     */
    public function toString(): string
    {
        $allowedValues = array_map(
            static function ($value): string {
                return VarDumper::dumpAsString($value);
            },
            $this->allowedValues,
        );

        $expectedAsString = implode(', ', $allowedValues);

        return "is one of $expectedAsString";
    }

    protected function matches($other): bool
    {
        return in_array($other, $this->allowedValues);
    }
}
