<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\helpers;

/**
 * Object that represents the removal of array value while performing [[ArrayHelper::merge()]].
 *
 * Usage example:
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
 * The result will be
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
     * Restores class state after using `var_export()`.
     *
     * @param array $state
     * @return UnsetArrayValue
     * @see https://www.php.net/manual/en/function.var-export.php
     * @since 2.0.16
     */
    public static function __set_state($state)
    {
        return new self();
    }
}
