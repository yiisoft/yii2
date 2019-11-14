<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\conditions;

use yii\base\InvalidArgumentException;

/**
 * 类 LikeCondition 表示 `LIKE` 条件。
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class LikeCondition extends SimpleCondition
{
    /**
     * @var array|false 如果字符不应转义，则为 false，
     * 如果转义是条件生成器的职责，则为 null 或 空数组。
     * 默认情况下设置为 null。
     */
    protected $escapingReplacements;


    /**
     * @param string $column 列名。
     * @param string $operator 要使用的操作符（例如：`LIKE`，`NOT LIKE`，`OR LIKE` 或 `OR NOT LIKE`）
     * @param string[]|string $value 应该与 $column 比较的单个值或值数组。
     * 如果是一个空数组，当操作符是 `LIKE` 或 `OR LIKE`时，生成的表达式则为 false，
     * 如果操作符是 `NOT LIKE` 或 `OR NOT LIKE`，生成的表达式则为空。
     */
    public function __construct($column, $operator, $value)
    {
        parent::__construct($column, $operator, $value);
    }

    /**
     * 此方法允许指定如何转义值中的特殊字符。
     *
     * @param array 从特殊字符到其转义对应字符的映射数组。
     * 你可以使用 `false` 或空数组来表示值已经转义，并不应该再应用转义。
     * 注意，使用转义映射（或未提供第三个操作数）时，
     * 值将自动包含在一对 % 字符中。
     */
    public function setEscapingReplacements($escapingReplacements)
    {
        $this->escapingReplacements = $escapingReplacements;
    }

    /**
     * @return array|false
     */
    public function getEscapingReplacements()
    {
        return $this->escapingReplacements;
    }

    /**
     * {@inheritdoc}
     * @throws InvalidArgumentException 如果给出错误操作数，则抛出 InvalidArgumentException 异常。
     */
    public static function fromArrayDefinition($operator, $operands)
    {
        if (!isset($operands[0], $operands[1])) {
            throw new InvalidArgumentException("Operator '$operator' requires two operands.");
        }

        $condition = new static($operands[0], $operator, $operands[1]);
        if (isset($operands[2])) {
            $condition->escapingReplacements = $operands[2];
        }

        return $condition;
    }
}
