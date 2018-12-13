<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\data;

/**
 * ActiveDataFilter 允许以适合 [[yiidbQueryInterface::where()]] 的格式组合过滤条件。
 *
 * @see DataFilter
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0.13
 */
class ActiveDataFilter extends DataFilter
{
    /**
     * @var array 过滤条件关键字与构建方法之间的映射关系。
     * 这些方法被 [[buildCondition()]] 方法用于构建实际的过滤条件。
     * 可以使用 PHP callback 指定特定的条件生成器。 例如：
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
     * @var array 将过滤操作符映射到 [[\yii\db\QueryInterface::where()]] 中使用的操作符。
     * 格式：`[filterOperator => queryOperator]`。
     * 如果映射中没有出现特定的操作符关键字，则按原样使用它。
     *
     * 通常，由于过滤器操作符名称与 [[yiidbQueryInterface::where()]] 中使用的名称一致，所以
     * 映射可以留空。然而，在某些特殊情况下，您可能需要对其进行调整。
     * 例如，在使用 PosgreSQL 时，您可能希望设置以下映射：
     *
     * ```php
     * [
     *     'LIKE' => 'ILIKE'
     * ]
     * ```
     */
    public $queryOperatorMap = [];


    /**
     * {@inheritdoc}
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
     * @return array 构建的条件
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
            $parts[] = $callback($key, $value);
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
     * 构建由多个独立条件组成的连接条件。
     * 它包括 `and` 和 `or` 等操作符。
     * @param string $operator 操作符关键字。
     * @param mixed $condition 原始条件。
     * @return array 实际条件。
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
     * 构建由单个条件组成的块条件。
     * 它包括 `not` 操作符。
     * @param string $operator 操作符关键字。
     * @param mixed $condition 原始条件。
     * @return array 实际条件。
     */
    protected function buildBlockCondition($operator, $condition)
    {
        if (isset($this->queryOperatorMap[$operator])) {
            $operator = $this->queryOperatorMap[$operator];
        }
        return [
            $operator,
            $this->buildCondition($condition),
        ];
    }

    /**
     * 建立一个搜索条件的特殊属性。
     * @param string $attribute 搜索属性名称。
     * @param mixed $condition 搜索条件。
     * @return array 实际条件。
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
                        $parts[] = $callback($operator, $value, $attribute);
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
     * 构建一个操作符条件。
     * @param string $operator 操作符关键字。
     * @param mixed $condition 属性条件。
     * @param string $attribute 属性名字。
     * @return array 实际条件。
     */
    protected function buildOperatorCondition($operator, $condition, $attribute)
    {
        if (isset($this->queryOperatorMap[$operator])) {
            $operator = $this->queryOperatorMap[$operator];
        }
        return [$operator, $attribute, $this->filterAttributeValue($attribute, $condition)];
    }
}
