<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\conditions;

use yii\db\ExpressionBuilderInterface;
use yii\db\ExpressionBuilderTrait;
use yii\db\ExpressionInterface;

/**
 * 类 ConjunctionConditionBuilder 构建抽象类的对象 [[ConjunctionCondition]]
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class ConjunctionConditionBuilder implements ExpressionBuilderInterface
{
    use ExpressionBuilderTrait;


    /**
     * 方法从 $expression 构建原始 SQL，
     * 不会被额外转义或引用。
     *
     * @param ExpressionInterface|ConjunctionCondition $condition 要构建的表达式。
     * @param array $params 绑定参数。
     * @return string 原始 SQL 不会被额外转义或引用。
     */
    public function build(ExpressionInterface $condition, array &$params = [])
    {
        $parts = $this->buildExpressionsFrom($condition, $params);

        if (empty($parts)) {
            return '';
        }

        if (count($parts) === 1) {
            return reset($parts);
        }

        return '(' . implode(") {$condition->getOperator()} (", $parts) . ')';
    }

    /**
     * 构建存储再 $condition 中的表达式
     *
     * @param ExpressionInterface|ConjunctionCondition $condition 要构建的表达式。
     * @param array $params 绑定参数
     * @return string[]
     */
    private function buildExpressionsFrom(ExpressionInterface $condition, &$params = [])
    {
        $parts = [];
        foreach ($condition->getExpressions() as $condition) {
            if (is_array($condition)) {
                $condition = $this->queryBuilder->buildCondition($condition, $params);
            }
            if ($condition instanceof ExpressionInterface) {
                $condition = $this->queryBuilder->buildExpression($condition, $params);
            }
            if ($condition !== '') {
                $parts[] = $condition;
            }
        }

        return $parts;
    }
}
