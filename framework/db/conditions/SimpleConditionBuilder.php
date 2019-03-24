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
use yii\db\Query;

/**
 * 类 SimpleConditionBuilder 构建 [[SimleCondition]] 的对象
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class SimpleConditionBuilder implements ExpressionBuilderInterface
{
    use ExpressionBuilderTrait;


    /**
     * 方法从 $expression 构建原始 SQL，
     * 不会被额外转义或引用。
     *
     * @param ExpressionInterface|SimpleCondition $expression 构建的表达式。
     * @param array $params 绑定参数。
     * @return string 原始 SQL 不会被转义或引用。
     */
    public function build(ExpressionInterface $expression, array &$params = [])
    {
        $operator = $expression->getOperator();
        $column = $expression->getColumn();
        $value = $expression->getValue();

        if (strpos($column, '(') === false) {
            $column = $this->queryBuilder->db->quoteColumnName($column);
        }

        if ($value === null) {
            return "$column $operator NULL";
        }
        if ($value instanceof ExpressionInterface) {
            return "$column $operator {$this->queryBuilder->buildExpression($value, $params)}";
        }

        $phName = $this->queryBuilder->bindParam($value, $params);
        return "$column $operator $phName";
    }
}
