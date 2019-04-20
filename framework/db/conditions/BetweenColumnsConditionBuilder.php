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
 * 类 BetweenColumnsConditionBuilder 构建 [[BetweenColumnsCondition]] 类的对象
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class BetweenColumnsConditionBuilder implements ExpressionBuilderInterface
{
    use ExpressionBuilderTrait;


    /**
     * 从不会被额外转义或引用的 $expression 接口
     * 构建原始 SQL 语句的方法。
     *
     * @param ExpressionInterface|BetweenColumnsCondition $expression 要构建的表达式。
     * @param array $params 绑定参数。
     * @return string 不会被额外转义或引用的 SQL语句。
     */
    public function build(ExpressionInterface $expression, array &$params = [])
    {
        $operator = $expression->getOperator();

        $startColumn = $this->escapeColumnName($expression->getIntervalStartColumn(), $params);
        $endColumn = $this->escapeColumnName($expression->getIntervalEndColumn(), $params);
        $value = $this->createPlaceholder($expression->getValue(), $params);

        return "$value $operator $startColumn AND $endColumn";
    }

    /**
     * 准备要在 SQL 语句中使用的列名。
     *
     * @param Query|ExpressionInterface|string $columnName
     * @param array $params 绑定参数。
     * @return string
     */
    protected function escapeColumnName($columnName, &$params = [])
    {
        if ($columnName instanceof Query) {
            list($sql, $params) = $this->queryBuilder->build($columnName, $params);
            return "($sql)";
        } elseif ($columnName instanceof ExpressionInterface) {
            return $this->queryBuilder->buildExpression($columnName, $params);
        } elseif (strpos($columnName, '(') === false) {
            return $this->queryBuilder->db->quoteColumnName($columnName);
        }

        return $columnName;
    }

    /**
     * 将 $value 附加到 $params 数组并返回占位符。
     *
     * @param mixed $value
     * @param array $params passed by reference
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
