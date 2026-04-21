<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db\conditions;

use ArrayAccess;
use yii\db\Expression;
use yii\db\ExpressionBuilderInterface;
use yii\db\ExpressionBuilderTrait;
use yii\db\ExpressionInterface;
use yii\db\Query;

use function count;
use function is_array;
use function is_string;

/**
 * Builds objects of [[InCondition]].
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class InConditionBuilder implements ExpressionBuilderInterface
{
    use ExpressionBuilderTrait;

    /**
     * Builds raw SQL from the expression.
     *
     * @param ExpressionInterface|InCondition $expression the expression to be built.
     * @param array $params the binding parameters.
     *
     * @return string the raw SQL that will not be additionally escaped or quoted.
     */
    public function build(ExpressionInterface $expression, array &$params = []): string
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

        if (!is_array($values)) {
            // ensure values is an array
            $values = [$values];
        }

        if (is_array($column)) {
            if (count($column) > 1) {
                return $this->buildCompositeInCondition($operator, $column, $values, $params);
            }
            $column = reset($column);
        }

        $nullCondition = null;
        $nullConditionOperator = null;

        if ($this->hasNullValue($column, $values)) {
            $nullCondition = $this->buildNullCondition($operator, $column, $params);

            $nullConditionOperator = $operator === 'IN' ? 'OR' : 'AND';
        }

        $sqlValues = $this->buildValues($expression, $values, $params);

        if ($sqlValues === []) {
            if ($nullCondition === null) {
                return $operator === 'IN' ? '0=1' : '';
            }

            return $nullCondition;
        }

        $column = $this->normalizeColumn($column, $params);

        if (count($sqlValues) > 1) {
            $sql = "$column $operator (" . implode(', ', $sqlValues) . ')';
        } else {
            $operator = $operator === 'IN' ? '=' : '<>';
            $sql = "{$column}{$operator}" . reset($sqlValues);
        }

        return $nullCondition !== null && $nullConditionOperator !== null
            ? "{$sql} {$nullConditionOperator} {$nullCondition}"
            : $sql;
    }

    /**
     * Builds values to be used in [[InCondition]].
     *
     * @param ConditionInterface|InCondition $condition the condition to be built.
     * @param array $values the values to bind.
     * @param array $params the binding parameters.
     *
     * @return array prepared SQL placeholders.
     */
    protected function buildValues(ConditionInterface $condition, array $values, array &$params): array
    {
        $sqlValues = [];

        $column = $condition->getColumn();

        if (is_array($column)) {
            $column = reset($column);
        }

        $columnKey = $this->resolveColumnKey($column);

        foreach ($values as $i => $value) {
            if (is_array($value) || $value instanceof ArrayAccess) {
                $value = $columnKey !== null ? ($value[$columnKey] ?? null) : null;
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
     * @param string $operator operator in uppercase.
     * @param array|string|ExpressionInterface $columns the columns to be matched.
     * @param Query $values subquery values.
     * @param array $params the binding parameters.
     *
     * @return string SQL for IN condition.
     */
    protected function buildSubqueryInCondition(
        string $operator,
        array|string|ExpressionInterface $columns,
        Query $values,
        array &$params
    ): string {
        $sql = $this->queryBuilder->buildExpression($values, $params);

        if (is_array($columns)) {
            foreach ($columns as $i => $column) {
                $columns[$i] = $this->normalizeColumn($column, $params);
            }

            return '(' . implode(', ', $columns) . ") $operator $sql";
        }

        return $this->normalizeColumn($columns, $params) . " $operator $sql";
    }

    /**
     * Builds SQL for composite IN condition.
     *
     * @param string $operator operator in uppercase.
     * @param array $columns columns to be matched.
     * @param array $values row values.
     * @param array $params the binding parameters.
     *
     * @return string SQL for composite IN condition.
     */
    protected function buildCompositeInCondition(
        string $operator,
        array $columns,
        array $values,
        array &$params
    ): string {
        $quotedColumns = [];
        $columnKeys = [];

        foreach ($columns as $i => $column) {
            $quotedColumns[$i] = $this->normalizeColumn($column, $params);
            $columnKeys[$i] = $this->resolveColumnKey($column);
        }

        $vss = [];
        $notEqualOperator = $this->getNotEqualOperator();

        foreach ($values as $value) {
            $vs = [];

            foreach ($columns as $i => $_) {
                $columnKey = $columnKeys[$i];
                $columnValue = null;

                if (
                    (is_array($value) || $value instanceof ArrayAccess)
                    && $columnKey !== null
                ) {
                    $columnValue = $value[$columnKey] ?? null;
                }

                if ($columnValue === null) {
                    $vs[] = $quotedColumns[$i] . ($operator === 'IN' ? ' IS' : ' IS NOT') . ' NULL';
                } else {
                    if ($columnValue instanceof ExpressionInterface) {
                        $placeholder = $this->queryBuilder->buildExpression($columnValue, $params);
                    } else {
                        $placeholder = $this->queryBuilder->bindParam($columnValue, $params);
                    }

                    $vs[] = $quotedColumns[$i] . ($operator === 'IN' ? ' = ' : " {$notEqualOperator} ") . $placeholder;
                }
            }

            $vss[] = '(' . implode($operator === 'IN' ? ' AND ' : ' OR ', $vs) . ')';
        }

        if ($vss === []) {
            return $operator === 'IN' ? '0=1' : '';
        }

        return '(' . implode($operator === 'IN' ? ' OR ' : ' AND ', $vss) . ')';
    }

    /**
     * Returns inequality operator for decomposed `NOT IN` conditions.
     *
     * @return string inequality operator.
     */
    protected function getNotEqualOperator(): string
    {
        return '<>';
    }

    /**
     * Builds is null/is not null condition for column based on operator
     *
     * @param string $operator
     * @param string $column
     * @return string is null or is not null condition
     * @since 2.0.31
     */
    protected function getNullCondition(string $operator, string $column): string
    {
        $column = $this->queryBuilder->db->quoteColumnName($column);

        return $column . ($operator === 'IN' ? ' IS NULL' : ' IS NOT NULL');
    }

    /**
     * Builds null condition for scalar and expression columns.
     *
     * @param string $operator operator in uppercase.
     * @param string|ExpressionInterface $column column to be matched.
     * @param array $params the binding parameters.
     *
     * @return string null condition SQL.
     */
    private function buildNullCondition(string $operator, string|ExpressionInterface $column, array &$params): string
    {
        if ($column instanceof ExpressionInterface) {
            return $this->normalizeColumn($column, $params) . ($operator === 'IN' ? ' IS NULL' : ' IS NOT NULL');
        }

        return $this->getNullCondition($operator, $column);
    }

    /**
     * Checks whether condition values include null after row extraction.
     *
     * @param string|ExpressionInterface $column column to be matched.
     * @param array $values the values to inspect.
     *
     * @return bool whether values include null.
     */
    private function hasNullValue(string|ExpressionInterface $column, array $values): bool
    {
        $columnKey = $this->resolveColumnKey($column);

        foreach ($values as $value) {
            if (is_array($value) || $value instanceof ArrayAccess) {
                $value = $columnKey !== null ? ($value[$columnKey] ?? null) : null;
            }

            if ($value === null) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resolves array lookup key for column values.
     *
     * @param string|ExpressionInterface $column column to be matched.
     *
     * @return string|null lookup key for row arrays.
     */
    private function resolveColumnKey(string|ExpressionInterface $column): ?string
    {
        if (is_string($column)) {
            return $column;
        }

        if ($column instanceof Expression) {
            return $column->expression;
        }

        return null;
    }

    /**
     * Normalizes column to SQL fragment.
     *
     * @param string|ExpressionInterface $column column to be matched.
     * @param array $params the binding parameters.
     *
     * @return string SQL fragment for column.
     */
    private function normalizeColumn(string|ExpressionInterface $column, array &$params): string
    {
        if ($column instanceof Expression) {
            return $this->quoteColumn($this->queryBuilder->buildExpression($column, $params));
        }

        if ($column instanceof ExpressionInterface) {
            return $this->queryBuilder->buildExpression($column, $params);
        }

        return $this->quoteColumn($column);
    }

    /**
     * Quotes column name if needed.
     *
     * @param string $column the column name.
     *
     * @return string quoted column name.
     */
    private function quoteColumn(string $column): string
    {
        return strpos($column, '(') === false
            ? $this->queryBuilder->db->quoteColumnName($column)
            : $column;
    }
}
