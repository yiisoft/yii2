<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * LooseSetInterface is an empty interface which can be attached to any class
 * that extends from [[BaseObject]] to disable strict checks of existing
 * properties. If a class implements this interface, object instances behave
 * like PHP objects and allow to set and read-back non-existing properties.
 *
 * Note though, that PHP still throws an error if a property is read which was
 * not set before.
 *
 * @since 2.1
 */
interface LooseSetInterface
{
}
