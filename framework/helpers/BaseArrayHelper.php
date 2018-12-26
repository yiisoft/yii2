<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

use Yii;
use yii\base\Arrayable;
use yii\base\InvalidArgumentException;

/**
 * BaseArrayHelper 为 [[ArrayHelper]] 提供了具体的实现方法。
 *
 * 不要使用 BaseArrayHelper 类。使用 [[ArrayHelper]] 类来代替。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class BaseArrayHelper
{
    /**
     * 将对象或者对象数组转换成数组。
     * @param object|array|string $object 要转成数组的对象
     * @param array $properties 从对象类的名称到需要将生成的数组结果集放入到属性中的映射。
     * 每个类的属性集合指定一个以下格式的数组：
     *
     * ```php
     * [
     *     'app\models\Post' => [
     *         'id',
     *         'title',
     *         // the key name in array result => property name
     *         'createTime' => 'created_at',
     *         // the key name in array result => anonymous function
     *         'length' => function ($post) {
     *             return strlen($post->content);
     *         },
     *     ],
     * ]
     * ```
     *
     * `ArrayHelper::toArray($post, $properties)` 调用此方法生成的数组集合可能如下：
     *
     * ```php
     * [
     *     'id' => 123,
     *     'title' => 'test',
     *     'createTime' => '2013-01-01 12:00AM',
     *     'length' => 301,
     * ]
     * ```
     *
     * @param bool $recursive 是否使用递归的方式将对象的属性转换为数组。
     * @return array 这种对象的数组表示
     */
    public static function toArray($object, $properties = [], $recursive = true)
    {
        if (is_array($object)) {
            if ($recursive) {
                foreach ($object as $key => $value) {
                    if (is_array($value) || is_object($value)) {
                        $object[$key] = static::toArray($value, $properties, true);
                    }
                }
            }

            return $object;
        } elseif (is_object($object)) {
            if (!empty($properties)) {
                $className = get_class($object);
                if (!empty($properties[$className])) {
                    $result = [];
                    foreach ($properties[$className] as $key => $name) {
                        if (is_int($key)) {
                            $result[$name] = $object->$name;
                        } else {
                            $result[$key] = static::getValue($object, $name);
                        }
                    }

                    return $recursive ? static::toArray($result, $properties) : $result;
                }
            }
            if ($object instanceof Arrayable) {
                $result = $object->toArray([], [], $recursive);
            } else {
                $result = [];
                foreach ($object as $key => $value) {
                    $result[$key] = $value;
                }
            }

            return $recursive ? static::toArray($result, $properties) : $result;
        }

        return [$object];
    }

    /**
     * 递归合并 2 个及以上的数组。
     * 如果每个数组元素有相同的字符串键值对，
     * 后者将会覆盖前者（不同于 array_merge_recursive）。
     * 如果两个数组都有数组类型的元素并且具有相同的键，
     * 那么将进行递归合并。
     * 对于整型键类型元素，后面数组中的元素将
     * 会被追加到前面的数组中去。
     * 你能够使用 [[UnsetArrayValue]] 对象从之前的数组中设置值或者
     * [[ReplaceArrayValue]] 强制替换原先的值来替代递归数组合并。
     * @param array $a 需要合并的数组
     * @param array $b 需要合并的数组。你能够指定额外的
     * 数组中的第三个参数，第四个参数等。
     * @return array 合并之后的数组（不改变原始数组。）
     */
    public static function merge($a, $b)
    {
        $args = func_get_args();
        $res = array_shift($args);
        while (!empty($args)) {
            foreach (array_shift($args) as $k => $v) {
                if ($v instanceof UnsetArrayValue) {
                    unset($res[$k]);
                } elseif ($v instanceof ReplaceArrayValue) {
                    $res[$k] = $v->value;
                } elseif (is_int($k)) {
                    if (array_key_exists($k, $res)) {
                        $res[] = $v;
                    } else {
                        $res[$k] = $v;
                    }
                } elseif (is_array($v) && isset($res[$k]) && is_array($res[$k])) {
                    $res[$k] = static::merge($res[$k], $v);
                } else {
                    $res[$k] = $v;
                }
            }
        }

        return $res;
    }

    /**
     * 检索具有给定键或属性名的数组元素或对象属性的值。
     * 如果这个数组中不存在键，将返回默认值。
     * 从对象中获取值时不使用。
     *
     * 数组中的键可以指定圆点来检索子数组中的值或者对象中包含的属性。
     * 特别是，如果键是 `x.y.z`，
     * 然后返回的值中像这样 `$array['x']['y']['z']` 或者 `$array->x->y->z`（如果 `$array` 是一个对象）。
     * 如果 `$array['x']` 或者 `$array->x` 既不是数组也不是对象，将返回默认值。
     * 注意如果数组已经有元素 `x.y.z`，然后它的值将被返回来替代遍历子数组。
     * 因此最好要做指定键值对的数组
     * 像这样 `['x', 'y', 'z']`。
     *
     * 以下是一些用法示例,
     *
     * ```php
     * // working with array
     * $username = \yii\helpers\ArrayHelper::getValue($_POST, 'username');
     * // working with object
     * $username = \yii\helpers\ArrayHelper::getValue($user, 'username');
     * // working with anonymous function
     * $fullName = \yii\helpers\ArrayHelper::getValue($user, function ($user, $defaultValue) {
     *     return $user->firstName . ' ' . $user->lastName;
     * });
     * // using dot format to retrieve the property of embedded object
     * $street = \yii\helpers\ArrayHelper::getValue($users, 'address.street');
     * // using an array of keys to retrieve the value
     * $value = \yii\helpers\ArrayHelper::getValue($versions, ['1.0', 'date']);
     * ```
     *
     * @param array|object $array 从对象或数组中进行提取
     * @param string|\Closure|array $key 数组元素的键名，数组当中的键或者对象当中的属性名称，或者一个返回值的匿名函数。
     * 匿名函数应该像这样签名：
     * `function($array, $defaultValue)`。
     * 在 2.0.4 版本中可以通过数组当中可用的键来传递。
     * @param mixed $default 如果指定的数组当中的键不存在则返回默认值。
     * 从对象当中获取值时不使用。
     * @return mixed 找到该元素当中的值并返回，否则直接返回默认的值。
     */
    public static function getValue($array, $key, $default = null)
    {
        if ($key instanceof \Closure) {
            return $key($array, $default);
        }

        if (is_array($key)) {
            $lastKey = array_pop($key);
            foreach ($key as $keyPart) {
                $array = static::getValue($array, $keyPart);
            }
            $key = $lastKey;
        }

        if (is_array($array) && (isset($array[$key]) || array_key_exists($key, $array))) {
            return $array[$key];
        }

        if (($pos = strrpos($key, '.')) !== false) {
            $array = static::getValue($array, substr($key, 0, $pos), $default);
            $key = substr($key, $pos + 1);
        }

        if (is_object($array)) {
            // this is expected to fail if the property does not exist, or __get() is not implemented
            // it is not reliably possible to check whether a property is accessible beforehand
            return $array->$key;
        } elseif (is_array($array)) {
            return (isset($array[$key]) || array_key_exists($key, $array)) ? $array[$key] : $default;
        }

        return $default;
    }

    /**
     * 在指定键的路径上将值写入关联数组。
     * 如果没有这样的关键路径，它将通过递归创建。
     * 如果键存在，就会被覆盖。
     *
     * ```php
     *  $array = [
     *      'key' => [
     *          'in' => [
     *              'val1',
     *              'key' => 'val'
     *          ]
     *      ]
     *  ];
     * ```
     *
     * `ArrayHelper::setValue($array, 'key.in.0', ['arr' => 'val']);` 的结果如下：
     *
     * ```php
     *  [
     *      'key' => [
     *          'in' => [
     *              ['arr' => 'val'],
     *              'key' => 'val'
     *          ]
     *      ]
     *  ]
     *
     * ```
     *
     * 这个
     * `ArrayHelper::setValue($array, 'key.in', ['arr' => 'val']);` 或者
     * `ArrayHelper::setValue($array, ['key', 'in'], ['arr' => 'val']);`
     * 生成的结果集如下：
     *
     * ```php
     *  [
     *      'key' => [
     *          'in' => [
     *              'arr' => 'val'
     *          ]
     *      ]
     *  ]
     * ```
     *
     * @param array $array 将值写入到数组中
     * @param string|array|null $path 你想将值写入到 `$array` 中的路径
     * 它的组成可以是将每个路径的描述用圆点连起来
     * 也可以用数组中的键来描述路径
     * 如果路径为空 `$array` 则被分配给 `$value`
     * @param mixed $value 被写入的值
     * @since 2.0.13
     */
    public static function setValue(&$array, $path, $value)
    {
        if ($path === null) {
            $array = $value;
            return;
        }

        $keys = is_array($path) ? $path : explode('.', $path);

        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (!isset($array[$key])) {
                $array[$key] = [];
            }
            if (!is_array($array[$key])) {
                $array[$key] = [$array[$key]];
            }
            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;
    }

    /**
     * 从数组中移除元素并返回值。
     * 如果数组中不存在键，则默认值将被返回。
     *
     * 用法示例，
     *
     * ```php
     * // $array = ['type' => 'A', 'options' => [1, 2]];
     * // working with array
     * $type = \yii\helpers\ArrayHelper::remove($array, 'type');
     * // $array content
     * // $array = ['options' => [1, 2]];
     * ```
     *
     * @param array $array 要从中提取值得数组
     * @param string $key 数组元素的键名
     * @param mixed $default 如果指定的键不存在则返回默认值
     * @return mixed|null 如果找到该元素的值，否则为默认值
     */
    public static function remove(&$array, $key, $default = null)
    {
        if (is_array($array) && (isset($array[$key]) || array_key_exists($key, $array))) {
            $value = $array[$key];
            unset($array[$key]);

            return $value;
        }

        return $default;
    }

    /**
     * 从数组中移除对应的值并返回移除的项。
     *
     * 比如，
     *
     * ```php
     * $array = ['Bob' => 'Dylan', 'Michael' => 'Jackson', 'Mick' => 'Jagger', 'Janet' => 'Jackson'];
     * $removed = \yii\helpers\ArrayHelper::removeValue($array, 'Jackson');
     * // result:
     * // $array = ['Bob' => 'Dylan', 'Mick' => 'Jagger'];
     * // $removed = ['Michael' => 'Jackson', 'Janet' => 'Jackson'];
     * ```
     *
     * @param array $array 查找值得数组
     * @param string $value 从这个数组中移除的值
     * @return array 返回从数组中移除的项
     * @since 2.0.11
     */
    public static function removeValue(&$array, $value)
    {
        $result = [];
        if (is_array($array)) {
            foreach ($array as $key => $val) {
                if ($val === $value) {
                    $result[$key] = $val;
                    unset($array[$key]);
                }
            }
        }

        return $result;
    }

    /**
     * 根据指定的键对数组进行索引和/或分组。
     * 输入的应该是多维数组或对象数组。
     *
     * 这个 $key 可以是子数组的键名，对象的属性名，
     * 或匿名函数返回的值将被用作键。
     *
     * $groups 是数组中的键，
     * 用于根据指定的键将输入数组分组为一个或多个子数组。
     *
     * 如果 `$key` 被指定为 `null` 或者与该键对应的元素的值除未指定的 `$groups`
     * 外为 `null` 那么该元素将被丢弃。
     *
     * 比如：
     *
     * ```php
     * $array = [
     *     ['id' => '123', 'data' => 'abc', 'device' => 'laptop'],
     *     ['id' => '345', 'data' => 'def', 'device' => 'tablet'],
     *     ['id' => '345', 'data' => 'hgi', 'device' => 'smartphone'],
     * ];
     * $result = ArrayHelper::index($array, 'id');
     * ```
     *
     * 结果会生成一个关联数组，这个键就是 `id` 属性的值
     *
     * ```php
     * [
     *     '123' => ['id' => '123', 'data' => 'abc', 'device' => 'laptop'],
     *     '345' => ['id' => '345', 'data' => 'hgi', 'device' => 'smartphone']
     *     // The second element of an original array is overwritten by the last element because of the same id
     * ]
     * ```
     *
     * 匿名函数也可以用作于分组数组当中。
     *
     * ```php
     * $result = ArrayHelper::index($array, function ($element) {
     *     return $element['id'];
     * });
     * ```
     *
     * `id` 将作为第三个参数传入到 `$array` 并按 `id` 进行分组：
     *
     * ```php
     * $result = ArrayHelper::index($array, null, 'id');
     * ```
     *
     * 结果将生成多维数组并按 `id` 进行一维分组，
     * 二维按索引 `device` 进行分组并生成 `data` 索引三维数组：
     *
     * ```php
     * [
     *     '123' => [
     *         ['id' => '123', 'data' => 'abc', 'device' => 'laptop']
     *     ],
     *     '345' => [ // all elements with this index are present in the result array
     *         ['id' => '345', 'data' => 'def', 'device' => 'tablet'],
     *         ['id' => '345', 'data' => 'hgi', 'device' => 'smartphone'],
     *     ]
     * ]
     * ```
     *
     * 通过键进行分组的数组中也可以使用匿名函数：
     *
     * ```php
     * $result = ArrayHelper::index($array, 'data', [function ($element) {
     *     return $element['id'];
     * }, 'device']);
     * ```
     *
     * 结果将返回一个多维数组一维按 `id` 分组，
     * 二维按索引 `device` 分组并且三维按索引 `data` 进行分组：
     *
     * ```php
     * [
     *     '123' => [
     *         'laptop' => [
     *             'abc' => ['id' => '123', 'data' => 'abc', 'device' => 'laptop']
     *         ]
     *     ],
     *     '345' => [
     *         'tablet' => [
     *             'def' => ['id' => '345', 'data' => 'def', 'device' => 'tablet']
     *         ],
     *         'smartphone' => [
     *             'hgi' => ['id' => '345', 'data' => 'hgi', 'device' => 'smartphone']
     *         ]
     *     ]
     * ]
     * ```
     *
     * @param array $array 需要索引或者分组的数组
     * @param string|\Closure|null $key 列名或者匿名函数的结果将用于对数组进行分组
     * @param string|string[]|\Closure[]|null $groups 数组当中的键，将用一个或多个键来对传入的数组进行分组。
     * 如果 $key 属性或者它的值的特定元素为空和 $groups 没有定义，数组的元素将被丢弃。
     * 因此，如果变量 $groups 是指定的，数组元素将被添加到没有任何键的数组当中。
     * 此参数自版本 2.0.8 起可用。
     * @return array 索引数组和/或分组数组
     */
    public static function index($array, $key, $groups = [])
    {
        $result = [];
        $groups = (array) $groups;

        foreach ($array as $element) {
            $lastArray = &$result;

            foreach ($groups as $group) {
                $value = static::getValue($element, $group);
                if (!array_key_exists($value, $lastArray)) {
                    $lastArray[$value] = [];
                }
                $lastArray = &$lastArray[$value];
            }

            if ($key === null) {
                if (!empty($groups)) {
                    $lastArray[] = $element;
                }
            } else {
                $value = static::getValue($element, $key);
                if ($value !== null) {
                    if (is_float($value)) {
                        $value = StringHelper::floatToString($value);
                    }
                    $lastArray[$value] = $element;
                }
            }
            unset($lastArray);
        }

        return $result;
    }

    /**
     * 返回数组中指定列的值。
     * 传入的数组类型可以是多维数组或者对象数组。
     *
     * 比如，
     *
     * ```php
     * $array = [
     *     ['id' => '123', 'data' => 'abc'],
     *     ['id' => '345', 'data' => 'def'],
     * ];
     * $result = ArrayHelper::getColumn($array, 'id');
     * // the result is: ['123', '345']
     *
     * // using anonymous function
     * $result = ArrayHelper::getColumn($array, function ($element) {
     *     return $element['id'];
     * });
     * ```
     *
     * @param array $array
     * @param int|string|\Closure $name
     * @param bool $keepKeys 是否保留数组的键。如果不保留，
     * 数组的结果的索引将被重新定义为整数。
     * @return array 返回列表的列值
     */
    public static function getColumn($array, $name, $keepKeys = true)
    {
        $result = [];
        if ($keepKeys) {
            foreach ($array as $k => $element) {
                $result[$k] = static::getValue($element, $name);
            }
        } else {
            foreach ($array as $element) {
                $result[] = static::getValue($element, $name);
            }
        }

        return $result;
    }

    /**
     * 从多维数组当中或者对象数组（key-value pairs）构建一个映射。
     * 那个 `$from` 和 `$to` 参数指定的键名或者属性名来设置映射。
     * 可选，可以根据 `$group` 变量对映射键值对进一步分组。
     *
     * 比如，
     *
     * ```php
     * $array = [
     *     ['id' => '123', 'name' => 'aaa', 'class' => 'x'],
     *     ['id' => '124', 'name' => 'bbb', 'class' => 'x'],
     *     ['id' => '345', 'name' => 'ccc', 'class' => 'y'],
     * ];
     *
     * $result = ArrayHelper::map($array, 'id', 'name');
     * // the result is:
     * // [
     * //     '123' => 'aaa',
     * //     '124' => 'bbb',
     * //     '345' => 'ccc',
     * // ]
     *
     * $result = ArrayHelper::map($array, 'id', 'name', 'class');
     * // the result is:
     * // [
     * //     'x' => [
     * //         '123' => 'aaa',
     * //         '124' => 'bbb',
     * //     ],
     * //     'y' => [
     * //         '345' => 'ccc',
     * //     ],
     * // ]
     * ```
     *
     * @param array $array
     * @param string|\Closure $from
     * @param string|\Closure $to
     * @param string|\Closure $group
     * @return array
     */
    public static function map($array, $from, $to, $group = null)
    {
        $result = [];
        foreach ($array as $element) {
            $key = static::getValue($element, $from);
            $value = static::getValue($element, $to);
            if ($group !== null) {
                $result[static::getValue($element, $group)][$key] = $value;
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * 检查给定数组是否包含指定键。
     * 此方法通过支持不区分大小写键的比较增强了 `array_key_exists()`
     * 函数。
     * @param string $key 检测的键名
     * @param array $array 需要检查键的数组
     * @param bool $caseSensitive 键的比较是否支持区分大小写
     * @return bool 数组是否包含指定的键
     */
    public static function keyExists($key, $array, $caseSensitive = true)
    {
        if ($caseSensitive) {
            // Function `isset` checks key faster but skips `null`, `array_key_exists` handles this case
            // http://php.net/manual/en/function.array-key-exists.php#107786
            return isset($array[$key]) || array_key_exists($key, $array);
        }

        foreach (array_keys($array) as $k) {
            if (strcasecmp($key, $k) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * 按一个或多个键对对象数组或者数组（具有相同结构）进行排序。
     * @param array $array 要排序的数组。调用此方法后数组将被修改。
     * @param string|\Closure|array $key 按 key(s) 进行排序。
     * 引用子数组的键名元素，对象的属性名，或一个匿名函数返回用于比较的值。
     * 匿名函数的签名应该是这样的：`function($item)`。
     * 要按多维键排序，需提供数组中的键。
     * @param int|array $direction 排序方向。它可以按照 `SORT_ASC` 或者 `SORT_DESC` 来排序。
     * 当按照不同排序方向的多个键排序时，使用数组进行排序。
     * @param int|array $sortFlag PHP 排序标记。包括有效的值
     * `SORT_REGULAR`，`SORT_NUMERIC`，`SORT_STRING`，`SORT_LOCALE_STRING`，`SORT_NATURAL` 和 `SORT_FLAG_CASE`。
     * 请参考 [PHP manual](http://php.net/manual/en/function.sort.php)
     * 获取更多详细信息。按具有不同排序标志的多个键排序时，使用数组中的标记排序。
     * @throws InvalidArgumentException 如果 $direction 或者 $sortFlag 参数的个数
     * 与 $key 参数的个数不一致。
     */
    public static function multisort(&$array, $key, $direction = SORT_ASC, $sortFlag = SORT_REGULAR)
    {
        $keys = is_array($key) ? $key : [$key];
        if (empty($keys) || empty($array)) {
            return;
        }
        $n = count($keys);
        if (is_scalar($direction)) {
            $direction = array_fill(0, $n, $direction);
        } elseif (count($direction) !== $n) {
            throw new InvalidArgumentException('The length of $direction parameter must be the same as that of $keys.');
        }
        if (is_scalar($sortFlag)) {
            $sortFlag = array_fill(0, $n, $sortFlag);
        } elseif (count($sortFlag) !== $n) {
            throw new InvalidArgumentException('The length of $sortFlag parameter must be the same as that of $keys.');
        }
        $args = [];
        foreach ($keys as $i => $key) {
            $flag = $sortFlag[$i];
            $args[] = static::getColumn($array, $key);
            $args[] = $direction[$i];
            $args[] = $flag;
        }

        // This fix is used for cases when main sorting specified by columns has equal values
        // Without it it will lead to Fatal Error: Nesting level too deep - recursive dependency?
        $args[] = range(1, count($array));
        $args[] = SORT_ASC;
        $args[] = SORT_NUMERIC;

        $args[] = &$array;
        call_user_func_array('array_multisort', $args);
    }

    /**
     * 将字符串数组中的特殊字符编码为 HTML 实体。
     * 默认情况下只对数组值进行编码。
     * 如果数组是一个值，此方法还将递归的进行编码。
     * 只有字符串值才会被编码。
     * @param array $data 将要被编码的数据
     * @param bool $valuesOnly 是否只对数组值进行编码。
     * 如果不是，数组的键和值将同时被编码。
     * @param string $charset 数据使用的字符集。如果没有设置，
     * [[\yii\base\Application::charset]] 将被使用。
     * @return array 返回编码的数据
     * @see http://www.php.net/manual/en/function.htmlspecialchars.php
     */
    public static function htmlEncode($data, $valuesOnly = true, $charset = null)
    {
        if ($charset === null) {
            $charset = Yii::$app ? Yii::$app->charset : 'UTF-8';
        }
        $d = [];
        foreach ($data as $key => $value) {
            if (!$valuesOnly && is_string($key)) {
                $key = htmlspecialchars($key, ENT_QUOTES | ENT_SUBSTITUTE, $charset);
            }
            if (is_string($value)) {
                $d[$key] = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, $charset);
            } elseif (is_array($value)) {
                $d[$key] = static::htmlEncode($value, $valuesOnly, $charset);
            } else {
                $d[$key] = $value;
            }
        }

        return $d;
    }

    /**
     * 将 HTML 实体解码为字符串数组中的相应字符。
     * 只有数组值将被默认解码。
     * 如果数组是一个值，此方法还将递归的进行解码。
     * 只有字符串值才会被解码。
     * @param array $data 将要被解码的数据
     * @param bool $valuesOnly 是否只对数组值进行解码。
     * 如果不是，数组的键和值都将被解码。
     * @return array 返回解码的数据
     * @see http://www.php.net/manual/en/function.htmlspecialchars-decode.php
     */
    public static function htmlDecode($data, $valuesOnly = true)
    {
        $d = [];
        foreach ($data as $key => $value) {
            if (!$valuesOnly && is_string($key)) {
                $key = htmlspecialchars_decode($key, ENT_QUOTES);
            }
            if (is_string($value)) {
                $d[$key] = htmlspecialchars_decode($value, ENT_QUOTES);
            } elseif (is_array($value)) {
                $d[$key] = static::htmlDecode($value);
            } else {
                $d[$key] = $value;
            }
        }

        return $d;
    }

    /**
     * 返回一个值，该值指示给定数组是否是关联数组。
     *
     * 如果数组的键都是字符串，那么数组就是关联的。如果 `$allStrings` 设置为假，
     * 如果数组的键中至少有一个是字符串，那么该数组将被视为关联数组。
     *
     * 注意，空数组不会被认为是关联的。
     *
     * @param array $array 将被检测的数组
     * @param bool $allStrings 数组键是否必须为所有字符串
     * 以便数组被视为关联的。
     * @return bool 返回数组是否是关联数组
     */
    public static function isAssociative($array, $allStrings = true)
    {
        if (!is_array($array) || empty($array)) {
            return false;
        }

        if ($allStrings) {
            foreach ($array as $key => $value) {
                if (!is_string($key)) {
                    return false;
                }
            }

            return true;
        }

        foreach ($array as $key => $value) {
            if (is_string($key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 返回一个值，该值指示给定数组是否是索引数组。
     *
     * 如果一个数组的所有键都是整数，那么该数组是索引数组。如果变量 `$consecutive` 设置为真，
     * 那么数组键必须是从 0 开始的连续序列。
     *
     * 注意，空数组将被认为是索引的。
     *
     * @param array $array 将被检测的数组
     * @param bool $consecutive 数组键是否必须是连续序列
     * 以便数组是不是索引数组。
     * @return bool 是否为索引数组
     */
    public static function isIndexed($array, $consecutive = false)
    {
        if (!is_array($array)) {
            return false;
        }

        if (empty($array)) {
            return true;
        }

        if ($consecutive) {
            return array_keys($array) === range(0, count($array) - 1);
        }

        foreach ($array as $key => $value) {
            if (!is_int($key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 检查是否为数组或 [[\Traversable]] 包含一个元素。
     *
     * 这个方法与 PHP 函数 [in_array()](http://php.net/manual/en/function.in-array.php) 相同
     * 但它还适用于实现 [[\Traversable]] 接口。
     * @param mixed $needle 寻找的值。
     * @param array|\Traversable $haystack 要寻找的值。
     * @param bool $strict 是否启用 (`===`) 比较。
     * @return bool 如果 `$needle` 存在于 `$haystack` 中返回 `true`，否则将返回 `false`。
     * @throws InvalidArgumentException 如果 `$haystack` 不能遍历也不是数组则返回异常。
     * @see http://php.net/manual/en/function.in-array.php
     * @since 2.0.7
     */
    public static function isIn($needle, $haystack, $strict = false)
    {
        if ($haystack instanceof \Traversable) {
            foreach ($haystack as $value) {
                if ($needle == $value && (!$strict || $needle === $value)) {
                    return true;
                }
            }
        } elseif (is_array($haystack)) {
            return in_array($needle, $haystack, $strict);
        } else {
            throw new InvalidArgumentException('Argument $haystack must be an array or implement Traversable');
        }

        return false;
    }

    /**
     * 检查变量是数组还是 [[\Traversable]]。
     *
     * 该方法与 PHP 函数 [is_array()](http://php.net/manual/en/function.is-array.php) 相同，
     * 但是，它还可以用于实现 [[\Traversable]] 接口。
     * @param mixed $var 被评估的变量。
     * @return bool 变量 $var 是否是一个数组
     * @see http://php.net/manual/en/function.is-array.php
     * @since 2.0.8
     */
    public static function isTraversable($var)
    {
        return is_array($var) || $var instanceof \Traversable;
    }

    /**
     * 检测是否为一个数组或者 [[\Traversable]] 是另一个数组的子集还是 [[\Traversable]]。
     *
     * 这个方法将返回 `true`，如果 `$needles` 所有的元素都包含在 `$haystack`。
     * 如果至少缺少一个元素的话，将被返回 `false`。
     * @param array|\Traversable $needles 这个值必须 **all** 在 `$haystack` 存在。
     * @param array|\Traversable $haystack 要搜索的值。
     * @param bool $strict 是否启用 (`===`) 比较。
     * @throws InvalidArgumentException 如果 `$haystack` 或者 `$needles` 既不能遍历也不是数组。
     * @return bool 如果 `$needles` 存在于 `$haystack` 返回 `true`，否则返回 `false`。
     * @since 2.0.7
     */
    public static function isSubset($needles, $haystack, $strict = false)
    {
        if (is_array($needles) || $needles instanceof \Traversable) {
            foreach ($needles as $needle) {
                if (!static::isIn($needle, $haystack, $strict)) {
                    return false;
                }
            }

            return true;
        }

        throw new InvalidArgumentException('Argument $needles must be an array or implement Traversable');
    }

    /**
     * 根据指定的规则筛选数组。
     *
     * 比如：
     *
     * ```php
     * $array = [
     *     'A' => [1, 2],
     *     'B' => [
     *         'C' => 1,
     *         'D' => 2,
     *     ],
     *     'E' => 1,
     * ];
     *
     * $result = \yii\helpers\ArrayHelper::filter($array, ['A']);
     * // $result will be:
     * // [
     * //     'A' => [1, 2],
     * // ]
     *
     * $result = \yii\helpers\ArrayHelper::filter($array, ['A', 'B.C']);
     * // $result will be:
     * // [
     * //     'A' => [1, 2],
     * //     'B' => ['C' => 1],
     * // ]
     *
     * $result = \yii\helpers\ArrayHelper::filter($array, ['B', '!B.C']);
     * // $result will be:
     * // [
     * //     'B' => ['D' => 2],
     * // ]
     * ```
     *
     * @param array $array 源数组
     * @param array $filters 定义应该从结果中保留或删除的数组键的规则。
     * 具体规则如下：
     * - `var` - `$array['var']` 将被留在数组中。
     * - 只有 `var.key` = `$array['var']['key'] 将留在数组中。
     * - `!var.key` = `$array['var']['key'] 将从结果集中移除。
     * @return array 过滤后的数组
     * @since 2.0.9
     */
    public static function filter($array, $filters)
    {
        $result = [];
        $forbiddenVars = [];

        foreach ($filters as $var) {
            $keys = explode('.', $var);
            $globalKey = $keys[0];
            $localKey = isset($keys[1]) ? $keys[1] : null;

            if ($globalKey[0] === '!') {
                $forbiddenVars[] = [
                    substr($globalKey, 1),
                    $localKey,
                ];
                continue;
            }

            if (!array_key_exists($globalKey, $array)) {
                continue;
            }
            if ($localKey === null) {
                $result[$globalKey] = $array[$globalKey];
                continue;
            }
            if (!isset($array[$globalKey][$localKey])) {
                continue;
            }
            if (!array_key_exists($globalKey, $result)) {
                $result[$globalKey] = [];
            }
            $result[$globalKey][$localKey] = $array[$globalKey][$localKey];
        }

        foreach ($forbiddenVars as $var) {
            list($globalKey, $localKey) = $var;
            if (array_key_exists($globalKey, $result)) {
                unset($result[$globalKey][$localKey]);
            }
        }

        return $result;
    }
}
