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
 * 类 InConditionBuilder 构建 [[InCondition]] 类的对象
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class InConditionBuilder implements ExpressionBuilderInterface
{
    use ExpressionBuilderTrait;


    /**
     * 从不会被额外转义或引用的 $expression 接口
     * 构建原始 SQL 语句的方法。
     *
     * @param ExpressionInterface|InCondition $expression 要构建的表达式。
     * @param array $params 绑定参数。
     * @return string 不会被额外转义或引用的原始 SQL 语句。
     */
    public function build(ExpressionInterface $expression, array &$params = [])
    {
        $operator = $expression->getOperator();
        $column = $expression->getColumn();
        $values = $expression->getValues();

        if ($column === []) {
            // no columns to test against
            return $operator === 'IN' ? '0=1' : '';
        }

        if ($values instanceof Query) {
            return $this->buildSubqueryInCondition($operator, $column, $values, $params);
        }

        if (!is_array($values) && !$values instanceof \Traversable) {
            // ensure values is an array
            $values = (array) $values;
        }
        if ($column instanceof \Traversable || ((is_array($column) || $column instanceof \Countable) && count($column) > 1)) {
            return $this->buildCompositeInCondition($operator, $column, $values, $params);
        }

        if (is_array($column)) {
            $column = reset($column);
        }

        $sqlValues = $this->buildValues($expression, $values, $params);
        if (empty($sqlValues)) {
            return $operator === 'IN' ? '0=1' : '';
        }

        if (strpos($column, '(') === false) {
            $column = $this->queryBuilder->db->quoteColumnName($column);
        }
        if (count($sqlValues) > 1) {
            return "$column $operator (" . implode(', ', $sqlValues) . ')';
        }

        $operator = $operator === 'IN' ? '=' : '<>';

        return $column . $operator . reset($sqlValues);
    }

    /**
     * 构建要在 [[InCondition]] 中使用的 $values
     *
     * @param ConditionInterface|InCondition $condition
     * @param array $values
     * @param array $params 绑定参数
     * @return array 为 SQL 占位符准备的数组
     */
    protected function buildValues(ConditionInterface $condition, $values, &$params)
    {
        $sqlValues = [];
        $column = $condition->getColumn();

        foreach ($values as $i => $value) {
            if (is_array($value) || $value instanceof \ArrayAccess) {
                $value = isset($value[$column]) ? $value[$column] : null;
            }
            if ($value === null) {
                $sqlValues[$i] = 'NULL';
            } elseif ($value instanceof ExpressionInterface) {
                $sqlValues[$i] = $this->queryBuilder->buildExpression($value, $params);
            } else {
                $sqlValues[$i] = $this->queryBuilder->bindParam($value, $params);
            }
        }

        return $sqlValues;
    }

    /**
     * 为 IN 条件构建 SQL。
     *
     * @param string $operator
     * @param array|string $columns
     * @param Query $values
     * @param array $params
     * @return string SQL
     */
    protected function buildSubqueryInCondition($operator, $columns, $values, &$params)
    {
        $sql = $this->queryBuilder->buildExpression($values, $params);

        if (is_array($columns)) {
            foreach ($columns as $i => $col) {
                if (strpos($col, '(') === false) {
                    $columns[$i] = $this->queryBuilder->db->quoteColumnName($col);
                }
            }

            return '(' . implode(', ', $columns) . ") $operator $sql";
        }

        if (strpos($columns, '(') === false) {
            $columns = $this->queryBuilder->db->quoteColumnName($columns);
        }

        return "$columns $operator $sql";
    }

    /**
     * 为 IN 条件构建 SQL。
     *
     * @param string $operator
     * @param array|\Traversable $columns
     * @param array $values
     * @param array $params
     * @return string SQL
     */
    protected function buildCompositeInCondition($operator, $columns, $values, &$params)
    {
        $vss = [];
        foreach ($values as $value) {
            $vs = [];
            foreach ($columns as $column) {
                if (isset($value[$column])) {
                    $vs[] = $this->queryBuilder->bindParam($value[$column], $params);
                } else {
                    $vs[] = 'NULL';
                }
            }
            $vss[] = '(' . implode(', ', $vs) . ')';
        }

        if (empty($vss)) {
            return $operator === 'IN' ? '0=1' : '';
        }

        $sqlColumns = [];
        foreach ($columns as $i => $column) {
            $sqlColumns[] = strpos($column, '(') === false ? $this->queryBuilder->db->quoteColumnName($column) : $column;
        }

        return '(' . implode(', ', $sqlColumns) . ") $operator (" . implode(', ', $vss) . ')';
    }
}
