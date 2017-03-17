<?php

namespace yiiunit;

/**
 * IsOneOfAssert asserts that the value is one of the expected values.
 */
class IsOneOfAssert extends \PHPUnit_Framework_Constraint
{
    private $allowedValues;

    /**
     * IsOneOfAssert constructor.
     * @param $allowedValues
     */
    public function __construct($allowedValues)
    {
        parent::__construct();
        $this->allowedValues = $allowedValues;
    }


    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        $expectedAsString = "'" . implode("', '", $this->allowedValues) . "'";
        return "is one of $expectedAsString";
    }

    /**
     * @inheritdoc
     */
    protected function matches($other)
    {
        return in_array($other, $this->allowedValues, false);
    }
}
