<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * StaticInstanceInterface 是为类提供静态实例的接口，
 * 它可用于获取无法在静态方法中表达的类 meta 信息。
 * 例如：DI 或行为进行的调整仅在对象级别显示，
 * 但在类（静态）级别也可能需要。
 *
 * 要实现 [[instance()]] 方法，可以使用 [[StaticInstanceTrait]]。
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0.13
 * @see StaticInstanceTrait
 */
interface StaticInstanceInterface
{
    /**
     * 返回静态类实例，可用于获取 meta 信息。
     * @param bool $refresh 是否重新创建静态实例，如果它已经被缓存。
     * @return static 类的实例。
     */
    public static function instance($refresh = false);
}
