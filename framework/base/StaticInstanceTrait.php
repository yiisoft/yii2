<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;

/**
 * StaticInstanceTrait 提供了满足 [[StaticInstanceInterface]] 接口的方法。
 *
 * @see StaticInstanceInterface
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0.13
 */
trait StaticInstanceTrait
{
    /**
     * @var static[] 静态实例的格式：`[className => object]`
     */
    private static $_instances = [];


    /**
     * 返回静态类实例，该实例可用于获取 meta 信息
     * @param bool $refresh 是否重新创建静态实例，如果它已经被缓存。
     * @return static 类的实例。
     */
    public static function instance($refresh = false)
    {
        $className = get_called_class();
        if ($refresh || !isset(self::$_instances[$className])) {
            self::$_instances[$className] = Yii::createObject($className);
        }
        return self::$_instances[$className];
    }
}
