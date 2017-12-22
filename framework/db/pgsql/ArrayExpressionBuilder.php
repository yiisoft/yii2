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
        $value = $expression->getValues();

        if ($value instanceof Query) {
            list ($sql, $params) = $this->queryBuilder->build($value, $params);
            return $this->buildSubqueryArray($sql, $expression);
        }

        if ($value === null) {
            return "'{}'";
        }

        $placeholders = [];
        if (is_array($value)) {
            if ($expression->getDimension() > 1) {
                foreach ($value as $item) {
                    $placeholders[] = $this->build($this->unnestArrayExpression($expression, $item), $params);
                }
            } else {
                foreach ($value as $item) {
                    if ($item instanceof Query) {
                        list ($sql, $params) = $this->queryBuilder->build($item, $params);
                        $placeholders[] = $this->buildSubqueryArray($sql, $expression);
                        continue;
                    }
                    if ($item instanceof ExpressionInterface) {
                        $placeholders[] = $this->queryBuilder->buildExpression($item, $params);
                        continue;
                    }

                    $placeholders[] = $placeholder = static::PARAM_PREFIX . count($params);
                    $params[$placeholder] = $item;
                }
            }
        }

        if (empty($placeholders)) {
            return "'{}'";
        }

        return 'ARRAY[' . implode(', ', $placeholders) . ']' . $this->getTypecast($expression);
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
     * @return string the typecast expression based on [[type]].
     */
    protected function getTypecast(ArrayExpression $expression)
    {
        if ($expression->getType() === null) {
            return '';
        }

        $result = '::' . $expression->getType();
        $result .= str_repeat('[]', $expression->getDimension());

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
