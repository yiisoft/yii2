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
 * @since 2.0.13
 */
class ActiveDataFilter extends DataFilter
{
    /**
     * @var array map of filter condition keywords to build methods.
     * These methods are used by [[buildCondition()]] to build the actual filter conditions.
     * Particular condition builder can be specified using a PHP callback. For example:
     *
     * ```php
     * [
     *     'XOR' => function (string $operator, mixed $condition) {
     *         //return array;
     *     },
     *     'LIKE' => function (string $operator, mixed $condition, string $attribute) {
     *         //return array;
     *     },
     * ]
     * ```
     */
    public $conditionBuilders = [
        'AND' => 'buildConjunctionCondition',
        'OR' => 'buildConjunctionCondition',
        'NOT' => 'buildBlockCondition',
        '<' => 'buildOperatorCondition',
        '>' => 'buildOperatorCondition',
        '<=' => 'buildOperatorCondition',
        '>=' => 'buildOperatorCondition',
        '=' => 'buildOperatorCondition',
        '!=' => 'buildOperatorCondition',
        'IN' => 'buildOperatorCondition',
        'NOT IN' => 'buildOperatorCondition',
        'LIKE' => 'buildOperatorCondition',
    ];
    /**
     * @var array a map from filter operators to the ones use in [[\yii\db\QueryInterface::where()]],
     * in format: `[filterOperator => queryOperator]`.
     * If particular operator keyword does not appear in the map, it will be used as it is.
     * Thus in general this field can be left empty as filter operator names are consistent with the ones
     * used at [[\yii\db\QueryInterface::where()]]. However, you may want to adjust it in some special cases.
     * For example: using PosgreSQL you may want to setup the following map:
     *
     * ```php
     * [
     *     'LIKE' => 'ILIKE'
     * ]
     * ```
     */
    public $queryOperatorMap = [];


    /**
     * @inheritdoc
     */
    protected function buildInternal()
    {
        $filter = $this->normalize(false);
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
                if (is_string($method)) {
                    $callback = [$this, $method];
                } else {
                    $callback = $method;
                }
            } else {
                $callback = [$this, 'buildAttributeCondition'];
            }
            $parts[] = call_user_func($callback, $key, $value);
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
     * This covers such operators like `and` and `or`.
     * @param string $operator operator keyword.
     * @param mixed $condition raw condition.
     * @return array actual condition.
     */
    protected function buildConjunctionCondition($operator, $condition)
    {
        if (isset($this->queryOperatorMap[$operator])) {
            $operator = $this->queryOperatorMap[$operator];
        }
        $result = [$operator];

        foreach ($condition as $part) {
            $result[] = $this->buildCondition($part);
        }

        return $result;
    }

    /**
     * Builds block condition, which consist of single condition.
     * This covers such operators like `not`.
     * @param string $operator operator keyword.
     * @param mixed $condition raw condition.
     * @return array actual condition.
     */
    protected function buildBlockCondition($operator, $condition)
    {
        if (isset($this->queryOperatorMap[$operator])) {
            $operator = $this->queryOperatorMap[$operator];
        }
        return [
            $operator,
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
                    if (isset($this->conditionBuilders[$operator])) {
                        $method = $this->conditionBuilders[$operator];
                        if (is_string($method)) {
                            $callback = [$this, $method];
                        } else {
                            $callback = $method;
                        }
                        $parts[] = call_user_func($callback, $operator, $value, $attribute);
                    } else {
                        $parts[] = $this->buildOperatorCondition($operator, $value, $attribute);
                    }
                }
            }

            if (!empty($parts)) {
                if (count($parts) > 1) {
                    return array_merge(['AND'], $parts);
                }
                return array_shift($parts);
            }
        }

        return [$attribute => $this->filterAttributeValue($attribute, $condition)];
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
        if (isset($this->queryOperatorMap[$operator])) {
            $operator = $this->queryOperatorMap[$operator];
        }
        return [$operator, $attribute, $this->filterAttributeValue($attribute, $condition)];
    }
}