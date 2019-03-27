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
 * 类 ConjunctionConditionBuilder 构建抽象类 [[ConjunctionCondition]] 的具体对象
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class ConjunctionConditionBuilder implements ExpressionBuilderInterface
{
    use ExpressionBuilderTrait;


    /**
     * 从不会被额外转义或引用的 $expression 接口
     * 构建原始 SQL 语句的方法。
     *
     * @param ExpressionInterface|ConjunctionCondition $condition 要构建的表达式。
     * @param array $params 绑定参数。
     * @return string 不会被额外转义或引用的原始 SQL 语句。
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
     * 构建存储在 $condition 中的表达式
     *
     * @param ExpressionInterface|ConjunctionCondition $condition 要构建的表达式。
     * @param array $params 绑定参数。
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
