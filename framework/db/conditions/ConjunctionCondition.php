<?php

namespace yii\db\conditions;

/**
 * Class ConjunctionCondition
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
abstract class ConjunctionCondition implements ConditionInterface
{
    /**
     * @var mixed[]
     */
    protected $expressions;

    /**
     * @param mixed $expressions
     */
    public function __construct($expressions) // TODO: use variadic params when PHP>5.6
    {
        $this->expressions = $expressions;
    }

    /**
     * @return mixed[]
     */
    public function getExpressions()
    {
        return $this->expressions;
    }

    /**
     * Returns the operator that is represented by this condition class, e.g. `AND`, `OR`.
     * @return string
     */
    abstract public function getOperator();

    /**
     * {@inheritdoc}
     */
    public static function fromArrayDefinition($operator, $operands)
    {
        return new static($operands);
    }
}
