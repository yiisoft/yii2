<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\conditions;

use yii\base\InvalidArgumentException;
use yii\db\Query;

/**
 * 表示 `EXISTS` 操作符的条件。
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class ExistsCondition implements ConditionInterface
{
    /**
     * @var string $operator 要使用的操作符（例如：`EXISTS` 或 `NOT EXISTS`）
     */
    private $operator;
    /**
     * @var Query 表示子查询的 [[Query]] 对象。
     */
    private $query;


    /**
     * ExistsCondition 构造函数。
     *
     * @param string $operator 要使用的操作符（例如：`EXISTS` 或 `NOT EXISTS`）
     * @param Query $query 表示子查询的 [[Query]] 对象。
     */
    public function __construct($operator, $query)
    {
        $this->operator = $operator;
        $this->query = $query;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArrayDefinition($operator, $operands)
    {
        if (!isset($operands[0]) || !$operands[0] instanceof Query) {
            throw new InvalidArgumentException('Subquery for EXISTS operator must be a Query object.');
        }

        return new static($operator, $operands[0]);
    }

    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @return Query
     */
    public function getQuery()
    {
        return $this->query;
    }
}
