<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\conditions;

use yii\base\InvalidArgumentException;

/**
 * 反转通过的条件 [[condition]]。
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class NotCondition implements ConditionInterface
{
    /**
     * @var mixed 要被否定的条件
     */
    private $condition;


    /**
     * NotCondition 构造函数。
     *
     * @param mixed $condition 要被否定的条件
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
     * @throws InvalidArgumentException 如果给出错误的操作数，则抛出 InvalidArgumentException 异常。
     */
    public static function fromArrayDefinition($operator, $operands)
    {
        if (count($operands) !== 1) {
            throw new InvalidArgumentException("Operator '$operator' requires exactly one operand.");
        }

        return new static(array_shift($operands));
    }
}
