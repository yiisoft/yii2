<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\pgsql;

use yii\db\ArrayExpression;
use yii\db\ExpressionBuilderInterface;
use yii\db\ExpressionBuilderTrait;
use yii\db\ExpressionInterface;
use yii\db\JsonExpression;
use yii\db\Query;

/**
 * ArrayExpressionBuilder 类为 PostgreSQL DBMS 构建 [[ArrayExpression]]。
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class ArrayExpressionBuilder implements ExpressionBuilderInterface
{
    use ExpressionBuilderTrait;


    /**
     * {@inheritdoc}
     * @param ArrayExpression|ExpressionInterface $expression 构建的表达式
     */
    public function build(ExpressionInterface $expression, array &$params = [])
    {
        $value = $expression->getValue();
        if ($value === null) {
            return 'NULL';
        }

        if ($value instanceof Query) {
            list ($sql, $params) = $this->queryBuilder->build($value, $params);
            return $this->buildSubqueryArray($sql, $expression);
        }

        $placeholders = $this->buildPlaceholders($expression, $params);

        return 'ARRAY[' . implode(', ', $placeholders) . ']' . $this->getTypehint($expression);
    }

    /**
     * 使用 $expression 值构建占位符数组
     * @param ExpressionInterface|ArrayExpression $expression
     * @param array $params 绑定参数。
     * @return array
     */
    protected function buildPlaceholders(ExpressionInterface $expression, &$params)
    {
        $value = $expression->getValue();

        $placeholders = [];
        if ($value === null || !is_array($value) && !$value instanceof \Traversable) {
            return $placeholders;
        }

        if ($expression->getDimension() > 1) {
            foreach ($value as $item) {
                $placeholders[] = $this->build($this->unnestArrayExpression($expression, $item), $params);
            }
            return $placeholders;
        }

        foreach ($value as $item) {
            if ($item instanceof Query) {
                list ($sql, $params) = $this->queryBuilder->build($item, $params);
                $placeholders[] = $this->buildSubqueryArray($sql, $expression);
                continue;
            }

            $item = $this->typecastValue($expression, $item);
            if ($item instanceof ExpressionInterface) {
                $placeholders[] = $this->queryBuilder->buildExpression($item, $params);
                continue;
            }

            $placeholders[] = $this->queryBuilder->bindParam($item, $params);
        }

        return $placeholders;
    }

    /**
     * @param ArrayExpression $expression
     * @param mixed $value
     * @return ArrayExpression
     */
    private function unnestArrayExpression(ArrayExpression $expression, $value)
    {
        $expressionClass = get_class($expression);

        return new $expressionClass($value, $expression->getType(), $expression->getDimension()-1);
    }

    /**
     * @param ArrayExpression $expression
     * @return string 基于 [[type]] 类型转换表达式。
     */
    protected function getTypehint(ArrayExpression $expression)
    {
        if ($expression->getType() === null) {
            return '';
        }

        $result = '::' . $expression->getType();
        $result .= str_repeat('[]', $expression->getDimension());

        return $result;
    }

    /**
     * 从子查询 SQL 语句构建数组表达式。
     *
     * @param string $sql 子查询语句。
     * @param ArrayExpression $expression
     * @return string 子查询数组表达式。
     */
    protected function buildSubqueryArray($sql, ArrayExpression $expression)
    {
        return 'ARRAY(' . $sql . ')' . $this->getTypehint($expression);
    }

    /**
     * 转换 $value 以便在 $expression 中使用
     *
     * @param ArrayExpression $expression
     * @param mixed $value
     * @return JsonExpression
     */
    protected function typecastValue(ArrayExpression $expression, $value)
    {
        if ($value instanceof ExpressionInterface) {
            return $value;
        }

        if (in_array($expression->getType(), [Schema::TYPE_JSON, Schema::TYPE_JSONB], true)) {
            return new JsonExpression($value);
        }

        return $value;
    }
}
