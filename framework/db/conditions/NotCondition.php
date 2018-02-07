<?php

namespace yii\db\conditions;

use yii\base\InvalidParamException;

/**
 * Condition that inverts passed [[condition]].
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class NotCondition implements ConditionInterface
{
    /**
     * @var mixed the condition to be negated
     */
    protected $condition;

    /**
     * NotCondition constructor.
     *
     * @param mixed $condition the condition to be negated
     */
    public function __construct($condition)
    {
        $this->condition = $condition;
    }

    /**
     * @return mixed
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * {@inheritdoc}
     * @throws InvalidParamException if wrong number of operands have been given.
     */
    public static function fromArrayDefinition($operator, $operands)
    {
        if (count($operands) !== 1) {
            throw new InvalidParamException("Operator '$operator' requires exactly one operand.");
        }

        return new static(array_shift($operands));
    }
}
