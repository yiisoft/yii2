<?php

namespace yii\db\pgsql;

use yii\db\ExpressionInterface;
use yii\db\Query;
use yii\db\QueryBuilder;

class ArrayType implements ExpressionInterface
{
    const PARAM_PREFIX = ':qp';

    protected $type;

    protected $value;

    /**
     * ArrayType constructor.
     *
     * @param $value
     * @param string|null $type
     */
    public function __construct($value, $type = null)
    {
        $this->value = $value;
        $this->type = $type;
    }

    protected function getTypecast()
    {
        if (!isset($this->type)) {
            return '';
        }

        $result = '::' . $this->type;
        if (!strpos($this->type, '[]')) {
            $result .= '[]';
        }

        return $result;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array $params
     * @return mixed
     */
    public function buildUsing(QueryBuilder $queryBuilder, &$params = [])
    {
        $placeholders = [];
        $value = $this->value;

        if ($value instanceof Query) {
            list ($sql, $newParams) = $queryBuilder->build($value, $params);
            $params = array_merge($params, $newParams);
            return $this->buildSubqueryArray($sql);
        }

        if (!is_array($value) && !$value instanceof \Traversable) {
            $value = [$value];
        }

        foreach ($value as $item) {
            if (is_array($item) || $item instanceof \Traversable) {
                $placeholders[] = (new self($item))->buildUsing($queryBuilder, $params);
                continue;
            }
            if ($item instanceof Query) {
                list ($sql, $newParams) = $queryBuilder->build($item, $params);
                $params = array_merge($params, $newParams);
                $placeholders[] = 'ARRAY(' . $sql . ')' . $this->getTypecast();
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

    protected function buildSubqueryArray($sql)
    {
        return 'ARRAY(' . $sql . ')' . $this->getTypecast();
    }
}
