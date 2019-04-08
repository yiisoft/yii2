<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\conditions;

use yii\base\InvalidArgumentException;

/**
 * 类 SimpleCondition 表示一个简单的条件，如 `“column” operator value`。
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class SimpleCondition implements ConditionInterface
{
    /**
     * @var string $operator 要使用的运算符。任何场景都可以使用，例如： `>`，`<=`，等等。
     */
    private $operator;
    /**
     * @var mixed [[operator]] 左侧的列名。
     */
    private $column;
    /**
     * @var mixed [[operator]] 右边的值。
     */
    private $value;


    /**
     * SimpleCondition 构造函数。
     *
     * @param mixed $column $operator 左边的字符
     * @param string $operator 要使用的运算符。任何场景下都可以使用，例如： `>`，`<=`，等等。
     * @param mixed $value $operator 右边的字符
     */
    public function __construct($column, $operator, $value)
    {
        $this->column = $column;
        $this->operator = $operator;
        $this->value = $value;
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
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     * @throws InvalidArgumentException 如果给出错误操作数，则抛出 InvalidArgumentException 异常。
     */
    public static function fromArrayDefinition($operator, $operands)
    {
        if (count($operands) !== 2) {
            throw new InvalidArgumentException("Operator '$operator' requires two operands.");
        }

        return new static($operands[0], $operator, $operands[1]);
    }
}
