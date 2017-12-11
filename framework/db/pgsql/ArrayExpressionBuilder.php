<?php

namespace yii\db\pgsql;

use yii\db\ArrayExpression;
use yii\db\ExpressionBuilderInterface;
use yii\db\ExpressionBuilderTrait;
use yii\db\ExpressionInterface;
use yii\db\Query;

/**
 * Class ArrayExpressionBuilder builds [[ArrayExpression]] for PostgreSQL DBMS.
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class ArrayExpressionBuilder implements ExpressionBuilderInterface
{
    use ExpressionBuilderTrait;

    const PARAM_PREFIX = ':qp';

    /**
     * {@inheritdoc}
     * @param ArrayExpression|ExpressionInterface $expression the expression to be built
     */
    public function build(ExpressionInterface $expression, &$params = [])
    {
        $expressionClass = get_class($expression);

        $value = $expression->getValues();

        if ($value instanceof Query) {
            list ($sql, $params) = $this->queryBuilder->build($value, $params);
            return $this->buildSubqueryArray($sql, $expression);
        }

        if (!is_array($value) && !$value instanceof \Traversable) {
            $value = [$value];
        }

        $placeholders = [];
        foreach ($value as $item) {
            if (is_array($item) || $item instanceof \Traversable) {
                $placeholders[] = $this->build(new $expressionClass($item), $params);
                continue;
            }
            if ($item instanceof Query) {
                list ($sql, $params) = $this->queryBuilder->build($item, $params);
                $placeholders[] = $this->buildSubqueryArray($sql, $expression);
                continue;
            }
            if ($item instanceof ExpressionInterface) {
                $placeholders[] = $this->queryBuilder->buildExpression($item, $params);
                continue;
            }
            if ($item === null) {
                continue;
            }

            $placeholders[] = $placeholder = static::PARAM_PREFIX . count($params);
            $params[$placeholder] = $item;
        }

        if (empty($placeholders)) {
            return "'{}'";
        }

        return 'ARRAY[' . implode(', ', $placeholders) . ']' . $this->getTypecast($expression);
    }

    /**
     * @param ArrayExpression $expression
     * @return string the typecast expression based on [[type]].
     */
    protected function getTypecast(ArrayExpression $expression)
    {
        if ($expression->getType() === null) {
            return '';
        }

        $result = '::' . $expression->getType();
        if (strpos($expression->getType(), '[]') === false) {
            $result .= '[]';
        }

        return $result;
    }

    /**
     * Build an array expression from a subquery SQL.
     *
     * @param string $sql the subquery SQL.
     * @param ArrayExpression $expression
     * @return string the subquery array expression.
     */
    protected function buildSubqueryArray($sql, ArrayExpression $expression)
    {
        return 'ARRAY(' . $sql . ')' . $this->getTypecast($expression);
    }
}
