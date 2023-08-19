<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db\conditions;

use yii\base\InvalidArgumentException;
use yii\db\ExpressionInterface;
use yii\db\Query;

/**
 * Class BetweenColumnCondition represents a `BETWEEN` condition where
 * values is between two columns. For example:
 *
 * ```php
 * new BetweenColumnsCondition(42, 'BETWEEN', 'min_value', 'max_value')
 * // Will be build to:
 * // 42 BETWEEN min_value AND max_value
 * ```
 *
 * And a more complex example:
 *
 * ```php
 * new BetweenColumnsCondition(
 *    new Expression('NOW()'),
 *    'NOT BETWEEN',
 *    (new Query)->select('time')->from('log')->orderBy('id ASC')->limit(1),
 *    'update_time'
 * );
 *
 * // Will be built to:
 * // NOW() NOT BETWEEN (SELECT time FROM log ORDER BY id ASC LIMIT 1) AND update_time
 * ```
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class BetweenColumnsCondition implements ConditionInterface
{
    /**
     * @var string $operator the operator to use (e.g. `BETWEEN` or `NOT BETWEEN`)
     */
    private $operator;
    /**
     * @var mixed the value to compare against
     */
    private $value;
    /**
     * @var string|ExpressionInterface|Query the column name or expression that is a beginning of the interval
     */
    private $intervalStartColumn;
    /**
     * @var string|ExpressionInterface|Query the column name or expression that is an end of the interval
     */
    private $intervalEndColumn;


    /**
     * Creates a condition with the `BETWEEN` operator.
     *
     * @param mixed the value to compare against
     * @param string $operator the operator to use (e.g. `BETWEEN` or `NOT BETWEEN`)
     * @param string|ExpressionInterface $intervalStartColumn the column name or expression that is a beginning of the interval
     * @param string|ExpressionInterface $intervalEndColumn the column name or expression that is an end of the interval
     */
    public function __construct($value, $operator, $intervalStartColumn, $intervalEndColumn)
    {
        $this->value = $value;
        $this->operator = $operator;
        $this->intervalStartColumn = $intervalStartColumn;
        $this->intervalEndColumn = $intervalEndColumn;
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
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string|ExpressionInterface|Query
     */
    public function getIntervalStartColumn()
    {
        return $this->intervalStartColumn;
    }

    /**
     * @return string|ExpressionInterface|Query
     */
    public function getIntervalEndColumn()
    {
        return $this->intervalEndColumn;
    }

    /**
     * {@inheritdoc}
     * @throws InvalidArgumentException if wrong number of operands have been given.
     */
    public static function fromArrayDefinition($operator, $operands)
    {
        if (!isset($operands[0], $operands[1], $operands[2])) {
            throw new InvalidArgumentException("Operator '$operator' requires three operands.");
        }

        return new static($operands[0], $operator, $operands[1], $operands[2]);
    }
}
