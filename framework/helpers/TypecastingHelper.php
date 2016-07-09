<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;
use yii\base\InvalidParamException;

/**
 * Class TypecastingHelper
 * @author Alexey Nalivaiko <nalliffunt@gmail.com>
 *
 * Helper class provide basic method for typecasting.
 *
 * In not strict mode if typecasting is impossible, helper returns raw passed value.
 *
 * @package yii\helpers
 */
class TypecastingHelper {
    const FLOAT_REG_EXP = '/^[+-]?(([0-9]+)|([0-9]*\.[0-9]+|[0-9]+\.[0-9]*)|(([0-9]+|([0-9]*\.[0-9]+|[0-9]+\.[0-9]*))[eE][+-]?[0-9]+))$/';

    const BOOLEAN = 'boolean';
    const INTEGER = 'integer';
    const STRING  = 'string';
    const FLOAT   = 'float';

    /**
     * @param $value
     * @param $type
     * @param bool $strict
     * @return bool|int|mixed|string
     */
    public static function typecastValue($value, $type, $strict  = true) {
        switch ($type) {
            case self::BOOLEAN : {
                return self::toBoolean($value, $strict);
            }
            case self::INTEGER : {
                return self::toInteger($value, $strict);
            }
            case self::STRING : {
                return self::toString($value, $strict);
            }
            case self::FLOAT : {
                return self::toFloat($value, $strict);
            }
            default : {
                if ($strict) {
                    Throw new InvalidParamException("Invalid type '${$type}'");
                }
                return $value;
            }
        }
    }

    /**
     * string value like 'false' OR 'FALSE' or 'N' or 'F' also equate false, in not strict mode
     * @param $value
     * @param bool $strict
     * @return bool
     */
    public function toBoolean($value, $strict = true) {
        if ($strict) {
            return (boolean)$value;
        }
        if (is_string($value) && in_array(trim(strtolower($value)), ['false', 'f', 'n'])) {
            return false;
        }

        return (boolean)$value;
    }

    /**
     * @param $value
     * @param bool $strict
     * @return int|mixed
     */
    public function toInteger($value, $strict = true) {
        if ($strict) {
            return (int)$value;
        }
        if (is_string($value) && (ctype_digit($value) || $value === '') || is_float($value) || is_bool($value)) {
            return (int)$value;
        }
        if (is_object($value) && method_exists($value, '__toString') && ctype_digit((string)$value)) {
            return (int)((string)$value);
        }

        return $value;
    }

    /**
     * @param mixed $value
     * @param bool $strict
     * @return string|mixed
     */
    public function toString($value, $strict = true) {
        if ($strict) {
            return (string)$value;
        }
        if (is_object($value) && method_exists($value, '__toString')) {
            return (string)$value;
        }
        if (!is_array($value)) {
            return (string)$value;
        }

        return $value;
    }

    /**
     * @param mixed $value
     * @param bool $strict
     * @return float|mixed
     */
    public function toFloat($value, $strict = true) {
        if ($strict) {
            return (float)$value;
        }
        if (is_string($value) && (ctype_digit($value) || $value === '' || preg_match(self::FLOAT_REG_EXP, $value))) {
            return (float)$value;
        }
        if (is_object($value) && method_exists($value, '__toString') && preg_match(self::FLOAT_REG_EXP, (string)$value)) {
            return (float)((string)$value);
        }

        return $value;
    }
}