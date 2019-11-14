<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

use yii\base\InvalidConfigException;

/**
 * 调用对象执行 [[ArrayHelper::merge()]] 方法后对数组中的值进行替换操作。
 *
 * 例如：
 *
 * ```php
 * $array1 = [
 *     'ids' => [
 *         1,
 *     ],
 *     'validDomains' => [
 *         'example.com',
 *         'www.example.com',
 *     ],
 * ];
 *
 * $array2 = [
 *     'ids' => [
 *         2,
 *     ],
 *     'validDomains' => new \yii\helpers\ReplaceArrayValue([
 *         'yiiframework.com',
 *         'www.yiiframework.com',
 *     ]),
 * ];
 *
 * $result = \yii\helpers\ArrayHelper::merge($array1, $array2);
 * ```
 *
 * 结果如下
 *
 * ```php
 * [
 *     'ids' => [
 *         1,
 *         2,
 *     ],
 *     'validDomains' => [
 *         'yiiframework.com',
 *         'www.yiiframework.com',
 *     ],
 * ]
 * ```
 *
 * @author Robert Korulczyk <robert@korulczyk.pl>
 * @since 2.0.10
 */
class ReplaceArrayValue
{
    /**
     * @var 用作替代的值。
     */
    public $value;


    /**
     * 构造函数。
     * @param 将 $value 值用作替换值。
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * 使用 `var_export()` 后进行实例化。
     *
     * @param array $state
     * @return ReplaceArrayValue
     * @throws InvalidConfigException 当 $state 数组中不包含 `value` 元素时，抛出异常
     * @see var_export()
     * @since 2.0.16
     */
    public static function __set_state($state)
    {
        if (!isset($state['value'])) {
            throw new InvalidConfigException('Failed to instantiate class "Instance". Required parameter "id" is missing');
        }

        return new self($state['value']);
    }
}
