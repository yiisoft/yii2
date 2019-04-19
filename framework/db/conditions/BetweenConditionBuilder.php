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
 * 类 BetweenConditionBuilder 构建 [[BetweenCondition]] 类的对象
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class BetweenConditionBuilder implements ExpressionBuilderInterface
{
    use ExpressionBuilderTrait;


    /**
     * 从不会被额外转义或引用的 $expression 接口
     * 构建原始 SQL 语句的方法。
     *
     * @param ExpressionInterface|BetweenCondition $expression 要构建的表达式。
     * @param array $params 绑定参数。
     * @return string 不会被额外转义或引用的 SQL语句。
     */
    public function build(ExpressionInterface $expression, array &$params = [])
    {
        $operator = $expression->getOperator();
        $column = $expression->getColumn();

        if (strpos($column, '(') === false) {
            $column = $this->queryBuilder->db->quoteColumnName($column);
        }

        $phName1 = $this->createPlaceholder($expression->getIntervalStart(), $params);
        $phName2 = $this->createPlaceholder($expression->getIntervalEnd(), $params);

        return "$column $operator $phName1 AND $phName2";
    }

    /**
     * 将 $value 附加到 $params 数组并返回占位符。
     *
     * @param mixed $value
     * @param array $params 通过引用传递
     * @return string
     */
    protected function createPlaceholder($value, &$params)
    {
        if ($value instanceof ExpressionInterface) {
            return $this->queryBuilder->buildExpression($value, $params);
        }

        return $this->queryBuilder->bindParam($value, $params);
    }
}
