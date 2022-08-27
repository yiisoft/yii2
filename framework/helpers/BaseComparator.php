<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\helpers;

use Closure;
use ReflectionFunction;

/**
 * The flexible comparation.
 *
 * @author info@ensostudio.ru
 * @since 2.0.47
 */
abstract class BaseComparator
{
    /**
     * The compares values must have same type.
     */
    const STRICT = 1;

    /**
     * The compares objects can be equal: instances of same class and their properties are equal (objects comparasion).
     */
    const EQUAL_OBJECT = 2;

    /**
     * The compares closures can be equal: defined in same file&line and their scopes are same (objects comparasion).
     */
    const EQUAL_CLOSURE = 4;

    /**
     * The compares float numbers can be equal: +/- epsilon (floats comparasion).
     */
    const EQUAL_FLOAT = 8;

    /**
     * The items/properties are equal if they are `NAN` (floats comparasion).
     */
    const EQUAL_NAN = 16;

    /**
     * The compares index arrays can be equal: values are equals, order no matter (arrays comparasion).
     */
    const EQUAL_ARRAY = 32;

    /**
     * The compares index arrays can be equal: values are equals, order no matter (arrays comparasion).
     */
    const EQUAL_INDEX_ARRAY = 64;

    /**
     * The binary safe case-insensitive string comparison (strings comparasion).
     */
    const EQUAL_STRING = 128;

    /**
     * The streams are equal if their meta data are equal (resources comparasion).
     */
    const EQUAL_STREAM = 256;

    /**
     * @var int The flags defines comparison behavior.
     */
    private $flags = 108;

    /**
     * Sets the behavior flags.
     *
     * @param int $flags The flags to control the comparasion behavior. It takes on either a bitmask, or self constants.
     * @return void
     */
    public static function setFlags($flags)
    {
        static::$flags = max(0, $flags);
    }

    /**
     * Gets the behavior flags.
     *
     * @return int
     */
    public static function getFlags()
    {
        return static::$flags;
    }

    /**
     * Checks if the given behavior flag is set by default.
     *
     * @param int $flag The behavior flag to check.
     * @return bool
     */
    public static function hasFlag($flag)
    {
        return (static::$flags & $flag) === $flag;
    }

    /**
     * Gets the PHP type of a variable.
     *
     * @param mixed $value the variable being type checked
     * @return string the type name
     */
    public static function getType($value)
    {
        $aliases = [
            'NULL' => 'null',
            'boolean' => 'bool',
            'integer' => 'int',
            'double' => 'float',
            'resource (closed)' => 'resource',
        ];
        $type = gettype($value);

        return isset($aliases[$type]) ? $aliases[$type] : $type;
    }

    /**
     * Checks whether PHP types are comparable (non-strict comparison).
     *
     * @param string $type the first type name
     * @param string $type2 the second type name
     * @return bool
     */
    public function canCompare($type, $type2)
    {
        if (
            ($type === 'object' && in_array($type2, ['int', 'float'], true))
            || ($type2 === 'object' && in_array($type, ['int', 'float'], true))
        ) {
            return false;
        }

        return true;
    }

    /**
     * Checks is the values are equal/same.
     *
     * @param mixed $value the first value to compare
     * @param mixed $value2 the second value to compare
     * @return bool
     */
    public static function compare($value, $value2)
    {
        if ($value === $value2) {
            return true;
        }

        $type = static::getType($value);
        $type2 = static::gettype($value2);

        if (static::hasFlag(static::STRICT) && $type !== $type2) {
            return false;
        }

        if (
            !static::hasFlag(static::STRICT)
            && static:canCompare($type, $type2)
            && $value == $value2
        ) {
            return true;
        }

        if (
            $type === $type2
            && in_array($type, ['array', 'object', 'resource', 'float', 'string'], true)
        ) {
            return static::{'compare' . $type . 's'}($value, $value2);
        }

        return false;
    }

    /**
     * Compares two decimal numbers.
     *
     * @param float $number the first number to compare
     * @param float $number2 the second number to compare
     * @return bool
     */
    protected static function compareFloats($number, $number2)
    {
        $number = (float) $number;
        $number2 = (float) $number2;

        $isNan = is_nan($number);
        $isNan2 = is_nan($number2);
        if ($isNan || $isNan2) {
            return $isNan && $isNan2 && static::hasFlag(static::EQUAL_NAN);
        }

        if (static::hasFlag(static::EQUAL_FLOAT)) {
            $epsilon = PHP_VERSION_ID < 70200 ? 0.0000000000000002 : PHP_FLOAT_EPSILON;

            return abs($number - $number2) < $epsilon
                || (min($number, $number2) + $epsilon === max($number, $number2) - $epsilon);
        }

        return false;
    }

    /**
     * Compares two strings.
     *
     * @param string $string the first string to compare
     * @param string $string2 the second string to compare
     * @return bool
     */
    protected static function compareStrings($string, $string2)
    {
        $string = (string) $string;
        $string2 = (string) $string2;

        $diff = static::hasFlag(static::EQUAL_STRING) ? strcasecmp($string, $string2) : strcmp($string, $string2);

        return $diff === 0;
    }

    /**
     * Compares two arrays.
     *
     * @param array $array the first array to compare
     * @param array $array2 the second array to compare
     * @return bool
     */
    protected static function compareArrays(array $array, array $array2)
    {
        if (count($array, COUNT_RECURSIVE) !== count($array2, COUNT_RECURSIVE)) {
            return false;
        }

        if (static::hasFlag(static::EQUAL_ARRAY)) {
            ksort($array);
            ksort($array2);
        }
        $keys = array_keys($array);
        if ($keys != array_keys($array2)) {
            return false;
        }

        if (static::hasFlag(static::EQUAL_INDEX_ARRAY) && $keys === array_keys($keys)) {
            // sort values in index arrays
            sort($array);
            sort($array2);
        }

        foreach ($array as $key => $value) {
            if (!static::compare($value, $array2[$key])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Compares two resources.
     *
     * @param resource $resource the first resource to compare
     * @param resource $resource2 the second resource to compare
     * @return bool
     */
    protected static function compareResources($resource, $resource2)
    {
        if (static::hasFlag(static::EQUAL_STREAM)) {
            $type = get_resource_type($resource);
            if ($type === 'stream' && $type === get_resource_type($resource2)) {
                return static::compareArrays(stream_get_meta_data($resource), stream_get_meta_data($resource2));
            }
        }

        return false;
    }

    /**
     * Compares two objects.
     *
     * @param object $object the first object to compare
     * @param object $object2 the second object to compare
     * @return bool
     */
    protected static function compareObjects($object, $object2)
    {
        if (get_class($object) !== get_class($object2)) {
            return false;
        }

        if ($object instanceof Closure) {
            if (static::hasFlag(static::EQUAL_CLOSURE)) {
                $rf = new ReflectionFunction($object);
                $rf2 = new ReflectionFunction($object2);
                $scope = $rf->getClosureThis();
                $scope2 = $rf2->getClosureThis();

                return (static::hasFlag(static::EQUAL_OBJECT) ? $scope == $scope2 : $scope === $scope2)
                    && (string) $rf === (string) $rf2;
            }

            return false;
        }

        if (static::hasFlag(static::EQUAL_OBJECT)) {
            return method_exists($object, '__toString')
                ? static::compareStrings((string) $object, (string) $object2)
                : static::compareArrays(get_object_vars($object), get_object_vars($object2));
        }

        return false;
    }
}
