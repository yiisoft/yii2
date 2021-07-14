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
 * Class InConditionBuilder builds objects of [[InCondition]]
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class InConditionBuilder implements ExpressionBuilderInterface
{
    use ExpressionBuilderTrait;


    /**
     * Method builds the raw SQL from the $expression that will not be additionally
     * escaped or quoted.
     *
     * @param ExpressionInterface|InCondition $expression the expression to be built.
     * @param array $params the binding parameters.
     * @return string the raw SQL that will not be additionally escaped or quoted.
     */
    public function build(ExpressionInterface $expression, array &$params = [])
    {
        $operator = strtoupper($expression->getOperator());
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

        if (is_array($column)) {
            if (count($column) > 1) {
                return $this->buildCompositeInCondition($operator, $column, $values, $params);
            }
            $column = reset($column);
        }

        if ($column instanceof \Traversable) {
            if (iterator_count($column) > 1) {
                return $this->buildCompositeInCondition($operator, $column, $values, $params);
            }
            $column->rewind();
            $column = $column->current();
        }

        if (is_array($values)) {
            $rawValues = $values;
        } elseif ($values instanceof \Traversable) {
            $rawValues = $this->getRawValuesFromTraversableObject($values);
        }

        $nullCondition = null;
        $nullConditionOperator = null;
        if (isset($rawValues) && in_array(null, $rawValues, true)) {
            $nullCondition = $this->getNullCondition($operator, $column);
            $nullConditionOperator = $operator === 'IN' ? 'OR' : 'AND';
        }

        $sqlValues = $this->buildValues($expression, $values, $params);
        if (empty($sqlValues)) {
            if ($nullCondition === null) {
                return $operator === 'IN' ? '0=1' : '';
            }
            return $nullCondition;
        }

        if (strpos($column, '(') === false) {
            $column = $this->queryBuilder->db->quoteColumnName($column);
        }
        if (count($sqlValues) > 1) {
            $sql = "$column $operator (" . implode(', ', $sqlValues) . ')';
        } else {
            $operator = $operator === 'IN' ? '=' : '<>';
            $sql = $column . $operator . reset($sqlValues);
        }

        return $nullCondition !== null && $nullConditionOperator !== null
            ? sprintf('%s %s %s', $sql, $nullConditionOperator, $nullCondition)
            : $sql;
    }

    /**
     * Builds $values to be used in [[InCondition]]
     *
     * @param ConditionInterface|InCondition $condition
     * @param array $values
     * @param array $params the binding parameters
     * @return array of prepared for SQL placeholders
     */
    protected function buildValues(ConditionInterface $condition, $values, &$params)
    {
        $sqlValues = [];
        $column = $condition->getColumn();

        if (is_array($column)) {
            $column = reset($column);
        }

        if ($column instanceof \Traversable) {
            $column->rewind();
            $column = $column->current();
        }

        foreach ($values as $i => $value) {
            if (is_array($value) || $value instanceof \ArrayAccess) {
                $value = isset($value[$column]) ? $value[$column] : null;
            }
            if ($value === null) {
                continue;
            } elseif ($value instanceof ExpressionInterface) {
                $sqlValues[$i] = $this->queryBuilder->buildExpression($value, $params);
            } else {
                $sqlValues[$i] = $this->queryBuilder->bindParam($value, $params);
            }
        }

        return $sqlValues;
    }

    /**
     * Builds SQL for IN condition.
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
     * Builds SQL for IN condition.
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

    /**
     * Builds is null/is not null condition for column based on operator
     *
     * @param string $operator
     * @param string $column
     * @return string is null or is not null condition
     * @since 2.0.31
     */
    protected function getNullCondition($operator, $column) {
        $column = $this->queryBuilder->db->quoteColumnName($column);
        if ($operator === 'IN') {
            return sprintf('%s IS NULL', $column);
        }
        return sprintf('%s IS NOT NULL', $column);
    }

    /**
     * @param \Traversable $traversableObject
     * @return array raw values
     * @since 2.0.31
     */
    protected function getRawValuesFromTraversableObject(\Traversable $traversableObject)
    {
        $rawValues = [];
        foreach ($traversableObject as $value) {
            if (is_array($value)) {
                $values = array_values($value);
                $rawValues = array_merge($rawValues, $values);
            } else {
                $rawValues[] = $value;
            }
        }
        return $rawValues;
    }
}
