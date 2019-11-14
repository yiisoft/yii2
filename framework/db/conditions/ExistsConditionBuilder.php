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
 * 类 ExistsConditionBuilder 构建 [[ExistsCondition]] 类的对象
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class ExistsConditionBuilder implements ExpressionBuilderInterface
{
    use ExpressionBuilderTrait;


    /**
     * 从不会被额外转义或引用的 $expression 接口
     * 构建原始 SQL 语句的方法。
     *
     * @param ExpressionInterface|ExistsCondition $expression 要构建的表达式。
     * @param array $params 绑定参数。
     * @return string 不会被额外转义或引用的原始 SQL 语句。
     */
    public function build(ExpressionInterface $expression, array &$params = [])
    {
        $operator = $expression->getOperator();
        $query = $expression->getQuery();

        $sql = $this->queryBuilder->buildExpression($query, $params);

        return "$operator $sql";
    }
}
