<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\conditions;

use yii\base\InvalidArgumentException;
use yii\db\ExpressionInterface;
use yii\db\Query;

/**
 * BetweenColumnCondition 类表示 `BETWEEN` 条件
 * 其中值在两个列中间。比如：
 *
 * ```php
 * new BetweenColumnsCondition(42, 'BETWEEN', 'min_value', 'max_value')
 * // Will be built to:
 * // 42 BETWEEN min_value AND max_value
 * ```
 *
 * 还有更加复杂的例子：
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
     * @var string $operator 要使用的运算符（例如：`BETWEEN` or `NOT BETWEEN`）
     */
    private $operator;
    /**
     * @var mixed 要比较的值
     */
    private $value;
    /**
     * @var string|ExpressionInterface|Query 作为间隔开头的列名称或表达式
     */
    private $intervalStartColumn;
    /**
     * @var string|ExpressionInterface|Query 作为间隔结尾的列名称或表达式
     */
    private $intervalEndColumn;


    /**
     * 使用 `BETWEEN` 运算符创建条件
     *
     * @param mixed 要比较的值
     * @param string $operator 要使用的运算符（例如：`BETWEEN` or `NOT BETWEEN`）
     * @param string|ExpressionInterface $intervalStartColumn 作为间隔开头的列名称或表达式
     * @param string|ExpressionInterface $intervalEndColumn 作为间隔结尾的列名称或表达式
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
     * @throws InvalidArgumentException 如果已给出错误的操作数，则抛出 InvalidArgumentException 异常。
     */
    public static function fromArrayDefinition($operator, $operands)
    {
        if (!isset($operands[0], $operands[1], $operands[2])) {
            throw new InvalidArgumentException("Operator '$operator' requires three operands.");
        }

        return new static($operands[0], $operator, $operands[1], $operands[2]);
    }
}
