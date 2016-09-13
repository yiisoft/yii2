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
 * @since 2.0
 */
class BaseArrayFilterHelper
{
    /**
     * @param array $models
     * @param array $conditions
     * @return array
     */
    public static function filterModels($models, $conditions)
    {
        return array_filter($models, function($model) use ($conditions) {
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

    protected static function checkAndCondition($model, $operator, $operands)
    {
        $result = true;
        foreach ($operands as $operand) {
            $result = $result && self::checkCondition($model, $operand);
        }
        return $result;
    }

    protected static function checkOrCondition($model, $operator, $operands)
    {
        $result = false;
        foreach ($operands as $operand) {
            $result = $result || self::checkCondition($model, $operand);
        }
        return $result;
    }

    protected static function checkNotCondition($model, $operator, $operands)
    {
        return !self::checkCondition($model, $operands[0]);
    }

    protected static function checkBetweenCondition($model, $operator, $operands)
    {
        if (!isset($operands[0], $operands[1], $operands[2])) {
            throw new InvalidParamException("Operator '$operator' requires three operands.");
        }
        list($property, $value1, $value2) = $operands;
        $result = $value1 < $model[$property] && $model[$property] < $value2;
        return strpos($operator, 'NOT') === false ? $result : !$result;
    }

    protected static function checkInCondition($model, $operator, $operands)
    {
        if (!isset($operands[0], $operands[1])) {
            throw new InvalidParamException("Operator '$operator' requires two operands.");
        }
        list($property, $values) = $operands;
        $result = in_array($model[$property], $values);
        return strpos($operator, 'NOT') === false ? $result : !$result;
    }

    protected static function checkLikeCondition($model, $operator, $operands)
    {
        if (!isset($operands[0], $operands[1])) {
            throw new InvalidParamException("Operator '$operator' requires two operands.");
        }
        list($property, $wildcard) = $operands;

        $wildcard = str_replace('%', '*', $wildcard);
        $wildcard = str_replace('_', '?', $wildcard);

        if(!isset($operands[2]) || $operands[2]) {
            $wildcard = '*' . $wildcard . '*';
        }

        $result = fnmatch($wildcard, $model[$property]);
        return strpos($operator, 'NOT') === false ? $result : !$result;
    }

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
