<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\conditions;

use yii\base\InvalidArgumentException;
use yii\db\ExpressionInterface;

/**
 * Class InCondition represents `IN` condition.
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class InCondition implements ConditionInterface
{
    /**
     * @var string $operator the operator to use (e.g. `IN` or `NOT IN`)
     */
    private $operator;
    /**
     * @var string|string[] the column name. If it is an array, a composite `IN` condition
     * will be generated.
     */
    private $column;
    /**
     * @var ExpressionInterface[]|string[]|int[] an array of values that [[column]] value should be among.
     * If it is an empty array the generated expression will be a `false` value if
     * [[operator]] is `IN` and empty if operator is `NOT IN`.
     */
    private $values;


    /**
     * SimpleCondition constructor
     *
     * @param string|string[] the column name. If it is an array, a composite `IN` condition
     * will be generated.
     * @param string $operator the operator to use (e.g. `IN` or `NOT IN`)
     * @param array an array of values that [[column]] value should be among. If it is an empty array the generated
     * expression will be a `false` value if [[operator]] is `IN` and empty if operator is `NOT IN`.
     */
    public function __construct($column, $operator, $values)
    {
        $this->column = $column;
        $this->operator = $operator;
        $this->values = $values;
    }

    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @return mixed
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @return ExpressionInterface[]|string[]|int[]
     */
    public function getValues()
    {
        return $this->values;
    }
    /**
     * {@inheritdoc}
     * @throws InvalidArgumentException if wrong number of operands have been given.
     */
    public static function fromArrayDefinition($operator, $operands)
    {
        if (!isset($operands[0], $operands[1])) {
            throw new InvalidArgumentException("Operator '$operator' requires two operands.");
        }

        return new static($operands[0], $operator, $operands[1]);
    }
}
