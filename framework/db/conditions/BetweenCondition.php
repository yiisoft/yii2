<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\conditions;

use yii\base\InvalidArgumentException;

/**
 * BetweenCondition 类表示 `BETWEEN` 条件。
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class BetweenCondition implements ConditionInterface
{
    /**
     * @var string $operator 要使用的运算符（例如：`BETWEEN` or `NOT BETWEEN`）
     */
    private $operator;
    /**
     * @var mixed [[operator]] 左侧的列名
     */
    private $column;
    /**
     * @var mixed 间隔开头
     */
    private $intervalStart;
    /**
     * @var mixed 间隔结尾
     */
    private $intervalEnd;


    /**
     * 使用 `BETWEEN` 运算符创建条件。
     *
     * @param mixed $column $operator 左边的文字
     * @param string $operator 要使用的运算符（例如：`BETWEEN` or `NOT BETWEEN`）
     * @param mixed $intervalStart 间隔开头
     * @param mixed $intervalEnd 间隔结尾
     */
    public function __construct($column, $operator, $intervalStart, $intervalEnd)
    {
        $this->column = $column;
        $this->operator = $operator;
        $this->intervalStart = $intervalStart;
        $this->intervalEnd = $intervalEnd;
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
     * @return mixed
     */
    public function getIntervalStart()
    {
        return $this->intervalStart;
    }

    /**
     * @return mixed
     */
    public function getIntervalEnd()
    {
        return $this->intervalEnd;
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
