<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

use yii\base\InvalidParamException;

/**
 * BaseArrayFilterHelper provides concrete implementation for [[ArrayFilterHelper]].
 *
 * Do not use BaseArrayFilterHelper. Use [[ArrayFilterHelper]] instead.
 *
 * @author Daniil Razorenov <daniltmb@mail.ru>
 */
class BaseArrayFilterHelper
{
    /**
     * Filter array by condition described by array syntax
     * @param array $models filtered by condition
     * @param array $conditions array syntax
     * @return array filtered models
     */
    public static function filterModels($models, $conditions)
    {
        return array_filter($models, function ($model) use ($conditions) {
            $result = true;
            foreach ($conditions as $condition) {
                $result = $result && self::checkCondition($model, $condition);
            }
            return $result;
        });
    }

    /**
     * @var array map of condition to check methods.
     * These methods are used by [[checkCondition]] to check conditions from array syntax.
     */
    protected static $checkConditions = [
        'NOT'           => 'checkNotCondition',
        'AND'           => 'checkAndCondition',
        'OR'            => 'checkOrCondition',
        'BETWEEN'       => 'checkBetweenCondition',
        'NOT BETWEEN'   => 'checkBetweenCondition',
        'IN'            => 'checkInCondition',
        'NOT IN'        => 'checkInCondition',
        'LIKE'          => 'checkLikeCondition',
        'NOT LIKE'      => 'checkLikeCondition',
    ];

    /**
     * Parses the condition specification and check the fulfillment of conditions for the model
     * @param mixed $model for which tests the condition
     * @param array $condition the condition specification. Please refer to [[Query::where()]]
     * on how to specify a condition.
     * @return boolean fulfillment of conditions
     */
    protected static function checkCondition($model, $condition)
    {
        if (isset($condition[0])) { // operator format: operator, operand 1, operand 2, ...
            $operator = strtoupper($condition[0]);
            if (isset(self::$checkConditions[$operator])) {
                $method = self::$checkConditions[$operator];
            } else {
                $method = 'checkSimpleCondition';
            }
            array_shift($condition);
            return self::$method($model, $operator, $condition);
        } else { // hash format: 'column1' => 'value1', 'column2' => 'value2', ...
            return self::checkHashCondition($model, $condition);
        }
    }

    /**
     * Check simple condition like `"property" operator value`.
     * @param mixed $model for which tests the condition
     * @param string $operator the operator to use. Anything could be used e.g. `>`, `<=`, etc.
     * @param array $operands contains two column names.
     * @return boolean fulfillment of conditions
     * @throws InvalidParamException if wrong number of operands have been given.
     */
    protected static function checkSimpleCondition($model, $operator, $operands)
    {
        if (count($operands) !== 2) {
            throw new InvalidParamException("Operator '$operator' requires two operands.");
        }
        list($property, $value) = $operands;
        switch ($operator) {
            case '<':
                return $model[$property] <  $value;
            case '<=':
                return $model[$property] <= $value;
            case '>':
                return $model[$property] >  $value;
            case '>=':
                return $model[$property] >= $value;
            case '!=':
                return $model[$property] != $value;
            case '==':
                return $model[$property] == $value;
        }
        throw new InvalidParamException("Operator '$operator' not supported.");
    }

    /**
     * Connects two condition with the `AND` operator.
     * @param mixed $model for which tests the condition
     * @param string $operator the operator to use for connecting the given operands
     * @param array $operands condition to connect.
     * @return boolean fulfillment of conditions
     */
    protected static function checkAndCondition($model, $operator, $operands)
    {
        $result = true;
        foreach ($operands as $operand) {
            $result = $result && self::checkCondition($model, $operand);
        }
        return $result;
    }

    /**
     * Connects two condition with the `OR` operator.
     * @param mixed $model for which tests the condition.
     * @param string $operator the operator to use for connecting the given operands.
     * @param array $operands condition to connect.
     * @return boolean fulfillment of conditions
     */
    protected static function checkOrCondition($model, $operator, $operands)
    {
        $result = false;
        foreach ($operands as $operand) {
            $result = $result || self::checkCondition($model, $operand);
        }
        return $result;
    }

    /**
     * Inverts condition with `NOT` operator.
     * @param mixed $model for which tests the condition.
     * @param string $operator the operator to use for connecting the given operands.
     * @param array $operands condition to connect.
     * @return boolean fulfillment of conditions
     */
    protected static function checkNotCondition($model, $operator, $operands)
    {
        return !self::checkCondition($model, $operands[0]);
    }

    /**
     * Check condition with the `BETWEEN` operator.
     * @param mixed $model for which tests the condition.
     * @param string $operator the operator to use (e.g. `BETWEEN` or `NOT BETWEEN`)
     * @param array $operands the first operand is the model property name. The second and third operands
     * describe the interval that property value should be in.
     * @return boolean fulfillment of conditions
     * @throws InvalidParamException if wrong number of operands have been given.
     */
    protected static function checkBetweenCondition($model, $operator, $operands)
    {
        if (!isset($operands[0], $operands[1], $operands[2])) {
            throw new InvalidParamException("Operator '$operator' requires three operands.");
        }
        list($property, $value1, $value2) = $operands;
        $result = $value1 < $model[$property] && $model[$property] < $value2;
        return strpos($operator, 'NOT') === false ? $result : !$result;
    }

    /**
     * Check condition with the `IN` operator.
     * @param mixed $model for which tests the condition.
     * @param string $operator the operator to use (e.g. `IN` or `NOT IN`)
     * @param array $operands the first operand is the model property name.
     * The second operand is an array of values that property value should be among.
     * @return boolean fulfillment of conditions
     * @throws InvalidParamException if wrong number of operands have been given.
     */
    protected static function checkInCondition($model, $operator, $operands)
    {
        if (!isset($operands[0], $operands[1])) {
            throw new InvalidParamException("Operator '$operator' requires two operands.");
        }
        list($property, $values) = $operands;
        $result = in_array($model[$property], $values);
        return strpos($operator, 'NOT') === false ? $result : !$result;
    }

    /**
     * Check condition with the `LIKE` operator.
     * @param mixed $model for which tests the condition.
     * @param string $operator the operator to use (e.g. `LIKE`, `NOT LIKE`)
     * @param array $operands an array of two or three operands
     *
     * - The first operand is the model property name.
     * - The second operand is a single value that property value
     *   should be compared with.
     * - An optional third operand can also be provided to specify how to escape special characters
     *   in the value(s). The operand should be an array of mappings from the special characters to their
     *   escaped counterparts. If this operand is not provided, a default escape mapping will be used.
     *   You may use `false` or an empty array to indicate the values are already escaped and no escape
     *   should be applied. Note that when using an escape mapping (or the third operand is not provided),
     *   the values will be automatically enclosed within a pair of percentage characters.
     * @return boolean fulfillment of conditions
     * @throws InvalidParamException if wrong number of operands have been given.
     */
    protected static function checkLikeCondition($model, $operator, $operands)
    {
        if (!isset($operands[0], $operands[1])) {
            throw new InvalidParamException("Operator '$operator' requires two operands.");
        }
        list($property, $wildcard) = $operands;

        $wildcard = str_replace('%', '*', $wildcard);
        $wildcard = str_replace('_', '?', $wildcard);

        if (!isset($operands[2]) || $operands[2]) {
            $wildcard = '*' . $wildcard . '*';
        }

        $result = fnmatch($wildcard, $model[$property]);
        return strpos($operator, 'NOT') === false ? $result : !$result;
    }

    /**
     * Creates a condition based on column-value pairs.
     * @param mixed $model for which tests the condition.
     * @param array $condition the condition specification.
     * @return boolean fulfillment of conditions
     */
    protected static function checkHashCondition($model, $condition)
    {
        $result = true;
        foreach ($condition as $property => $value) {
            if (ArrayHelper::isTraversable($value)) {
                // IN condition
                $result = $result && self::checkInCondition($model, 'IN', [$property, $value]);
            } else {
                if ($value === null) {
                    $result = $result && $model[$property] == null;
                } else {
                    $result = $result && $model[$property] == $value;
                }
            }
        }
        return $result;
    }
}
