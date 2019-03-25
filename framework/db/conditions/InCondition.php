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
 * 类 InCondition 表示 `IN` 条件。
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class InCondition implements ConditionInterface
{
    /**
     * @var string $operator 要使用的操作符（例如：`IN` 或 `NOT IN`）
     */
    private $operator;
    /**
     * @var string|string[] 列名。如果其是数组，
     * 将生成复合 `IN` 条件。
     */
    private $column;
    /**
     * @var ExpressionInterface[]|string[]|int[] 应该包含 [[column]] 值的值数组。
     * 如果值是一个空数组，当 [[operator]] 是 `IN` 时,则生成的表达式将会是 `false`,
     * 如果操作符是 `NOT IN`，则为空。
     */
    private $values;


    /**
     * SimpleCondition 构造函数
     *
     * @param string|string[] 列名。如果其是数组，
     * 将生成复合 `IN` 条件。
     * @param string $operator 要使用操作符（例如：`IN` 或 `NOT IN`）
     * @param array 包含 [[column]] 值的值数组。如果值是一个空数组，当 [[operator]] 是 `IN` 时，则生成的表达式将会是 `false`,
     * 如果操作符是 `NOT IN`，则为空。
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
     * @throws InvalidArgumentException 如果已给出错误的操作数，则抛出 InvalidArgumentException 异常。
     */
    public static function fromArrayDefinition($operator, $operands)
    {
        if (!isset($operands[0], $operands[1])) {
            throw new InvalidArgumentException("Operator '$operator' requires two operands.");
        }

        return new static($operands[0], $operator, $operands[1]);
    }
}
