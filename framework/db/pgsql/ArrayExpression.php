<?php

namespace yii\db\pgsql;

use yii\db\ExpressionInterface;
use yii\db\Query;
use yii\db\QueryBuilder;
use yii\db\QueryInterface;

/**
 * ArrayExpression represents a SQL expression that represents a PostreSQL array.
 *
 * Expressions of this type can be used for example in conditions, like:
 *
 * ```php
 * $query->andWhere(['@>', 'items', new ArrayExpression([1, 2, 3], 'integer')])
 * ```
 *
 * which will result in a condition `WHERE "items" @> ARRAY[1, 2, 3]::integer[]`.
 *
 * @see https://www.postgresql.org/docs/9.6/static/arrays.html
 * @see https://www.postgresql.org/docs/current/static/functions-array.html
 * @author Dmitry Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.13
 */
class ArrayExpression implements ExpressionInterface
{
    const PARAM_PREFIX = ':qp';

    /**
     * @var null|string the type of the array elements. Defaults to `null` which means the type is
     * not explicitly specified. This may result in an error if the type can not be inferred from the context.
     * @see https://www.postgresql.org/docs/9.6/static/arrays.html
     */
    protected $type;
    /**
     * @var array|QueryInterface|mixed the array content. Either represented as an array of values or a Query that
     * returns these values. A single value will be considered as an array containing one element.
     */
    protected $values;


    /**
     * ArrayExpression constructor.
     *
     * @param array|QueryInterface|mixed $values the array content. Either represented as an array of values or a Query that
     * returns these values. A single value will be considered as an array containing one element.
     * @param string|null $type the type of the array elements. Defaults to `null` which means the type is
     * not explicitly specified. This may result in an error if the type can not be inferred from the context.
     */
    public function __construct($values, $type = null)
    {
        $this->values = $values;
        $this->type = $type;
    }

    /**
     * @return string the typecast expression based on [[type]].
     */
    protected function getTypecast()
    {
        if ($this->type === null) {
            return '';
        }

        $result = '::' . $this->type;
        if (strpos($this->type, '[]') === false) {
            $result .= '[]';
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function buildUsing(QueryBuilder $queryBuilder, &$params = [])
    {
        $value = $this->values;

        if ($value instanceof Query) {
            list ($sql, $params) = $queryBuilder->build($value, $params);
            return $this->buildSubqueryArray($sql);
        }

        if (!is_array($value) && !$value instanceof \Traversable) {
            $value = [$value];
        }

        $placeholders = [];
        foreach ($value as $item) {
            if (is_array($item) || $item instanceof \Traversable) {
                $placeholders[] = (new self($item))->buildUsing($queryBuilder, $params);
                continue;
            }
            if ($item instanceof Query) {
                list ($sql, $params) = $queryBuilder->build($item, $params);
                $placeholders[] = $this->buildSubqueryArray($sql);
                continue;
            }
            if ($item instanceof ExpressionInterface) {
                $placeholders[] = $item->buildUsing($queryBuilder, $params);
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

        return 'ARRAY[' . implode(', ', $placeholders) . ']' . $this->getTypecast();
    }

    /**
     * Build an array expression from a subquery SQL.
     * @param string $sql the subquery SQL.
     * @return string the subquery array expression.
     */
    protected function buildSubqueryArray($sql)
    {
        return 'ARRAY(' . $sql . ')' . $this->getTypecast();
    }
}
