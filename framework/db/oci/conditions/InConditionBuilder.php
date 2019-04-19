<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\oci\conditions;

use yii\db\conditions\InCondition;
use yii\db\ExpressionInterface;

/**
 * {@inheritdoc}
 */
class InConditionBuilder extends \yii\db\conditions\InConditionBuilder
{
    /**
     * 从不会被额外转义或引用的 $expression 接口
     * 构建原始 SQL 语句的方法。
     *
     * @param ExpressionInterface|InCondition $expression 要构建的表达式。
     * @param array $params 绑定参数。
     * @return string 不会被额外转义或引用的 SQL 语句。
     */
    public function build(ExpressionInterface $expression, array &$params = [])
    {
        $splitCondition = $this->splitCondition($expression, $params);
        if ($splitCondition !== null) {
            return $splitCondition;
        }

        return parent::build($expression, $params);
    }

    /**
     * Oracle DBMS 中 `IN` 操作符后面的参数不能超过 1000 个。
     * 此方法将长的 `IN` 条件拆分为一系列较小的条件。
     *
     * @param ExpressionInterface|InCondition $condition 要构建的表达式。
     * @param array $params 绑定参数。
     * @return null|string null 不需要拆分时返回 null。否则，构建 SQL 条件。
     */
    protected function splitCondition(InCondition $condition, &$params)
    {
        $operator = $condition->getOperator();
        $values = $condition->getValues();
        $column = $condition->getColumn();

        if ($values instanceof \Traversable) {
            $values = iterator_to_array($values);
        }

        if (!is_array($values)) {
            return null;
        }

        $maxParameters = 1000;
        $count = count($values);
        if ($count <= $maxParameters) {
            return null;
        }

        $slices = [];
        for ($i = 0; $i < $count; $i += $maxParameters) {
            $slices[] = $this->queryBuilder->createConditionFromArray([$operator, $column, array_slice($values, $i, $maxParameters)]);
        }
        array_unshift($slices, ($operator === 'IN') ? 'OR' : 'AND');

        return $this->queryBuilder->buildCondition($slices, $params);
    }
}
