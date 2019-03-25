<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\conditions;

/**
 * ConjunctionCondition 类
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
     * 返回由此抽象类表示的操作符，例如：`AND`，`OR`。
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
