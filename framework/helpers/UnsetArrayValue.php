<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

/**
 * 该对象调用 [[ArrayHelper::merge()]] 方法后对数组中的值进行移除操作。
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
 *     'validDomains' => new \yii\helpers\UnsetArrayValue(),
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
 * ]
 * ```
 *
 * @author Robert Korulczyk <robert@korulczyk.pl>
 * @since 2.0.10
 */
class UnsetArrayValue
{
    /**
     * 使用 `var_export()` 后进行实例化。
     *
     * @param array $state
     * @return UnsetArrayValue
     * @see var_export()
     * @since 2.0.16
     */
    public static function __set_state($state)
    {
        return new self();
    }
}
