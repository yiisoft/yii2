<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rest;

/**
 * ActiveDataFilter allows composition of the filter condition in format suitable for [[\yii\db\QueryInterface::where()]].
 *
 * @see DataFilter
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0.10
 */
class ActiveDataFilter extends DataFilter
{
    /**
     * @var array map of filter condition keywords to build methods.
     * These methods are used by [[buildCondition]] to build the actual filter conditions.
     */
    public $conditionBuilders = [
        '$and' => 'buildConjunctionCondition',
        '$or' => 'buildConjunctionCondition',
        '$not' => 'buildBlockCondition',
        '$lt' => 'buildOperatorCondition',
        '$gt' => 'buildOperatorCondition',
        '$lte' => 'buildOperatorCondition',
        '$gte' => 'buildOperatorCondition',
        '$eq' => 'buildOperatorCondition',
        '$neq' => 'buildOperatorCondition',
        '$in' => 'buildOperatorCondition',
        '$nin' => 'buildOperatorCondition',
    ];
    /**
     * @var array
     */
    public $operatorMap = [
        '$and' => 'AND',
        '$or' => 'OR',
        '$not' => 'NOT',
        '$lt' => '<',
        '$gt' => '>',
        '$lte' => '<=',
        '$gte' => '>=',
        '$eq' => '==',
        '$neq' => '!=',
        '$in' => 'IN',
        '$nin' => 'NOT IN',
    ];

    /**
     * @inheritdoc
     */
    protected function buildInternal()
    {
        $filter = $this->getFilter();
        if (empty($filter)) {
            return [];
        }

        return $this->buildCondition($filter);
    }

    /**
     * @param array $condition
     * @return array built condition.
     */
    protected function buildCondition($condition)
    {
        $parts = [];
        foreach ($condition as $key => $value) {
            if (isset($this->conditionBuilders[$key])) {
                $method = $this->conditionBuilders[$key];
            } else {
                $method = 'buildAttributeCondition';
            }
            $parts[] = $this->$method($key, $value);
        }

        if (!empty($parts)) {
            if (count($parts) > 1) {
                $parts = array_merge(['AND'], $parts);
            } else {
                $parts = array_shift($parts);
            }
        }

        return $parts;
    }

    /**
     * Builds conjunction condition, which consist of multiple independent ones.
     * This covers such operators like `$and` and `$or`.
     * @param string $operator operator keyword.
     * @param mixed $condition raw condition.
     * @return array actual condition.
     */
    protected function buildConjunctionCondition($operator, $condition)
    {
        $result = [$this->operatorMap[$operator]];

        foreach ($condition as $part) {
            $result[] = $this->buildCondition($part);
        }

        return $result;
    }

    /**
     * Builds block condition, which consist of single condition.
     * This covers such operators like `$not`.
     * @param string $operator operator keyword.
     * @param mixed $condition raw condition.
     * @return array actual condition.
     */
    protected function buildBlockCondition($operator, $condition)
    {
        return [
            $this->operatorMap[$operator],
            $this->buildCondition($condition)
        ];
    }

    /**
     * Builds search condition for particular attribute.
     * @param string $attribute search attribute name.
     * @param mixed $condition search condition.
     * @return array actual condition.
     */
    protected function buildAttributeCondition($attribute, $condition)
    {
        if (is_array($condition)) {
            $parts = [];
            foreach ($condition as $operator => $value) {
                if (isset($this->operatorTypes[$operator])) {
                    $parts[] = $this->buildOperatorCondition($operator, $condition, $attribute);
                }
            }

            if (!empty($parts)) {
                return $parts;
            }
        }

        return [$attribute => $condition];
    }

    /**
     * Builds operator condition.
     * @param string $operator operator keyword.
     * @param mixed $condition attribute condition.
     * @param string $attribute attribute name.
     * @return array actual condition.
     */
    protected function buildOperatorCondition($operator, $condition, $attribute)
    {
        return [$this->operatorMap[$operator], $attribute, $condition];
    }
}