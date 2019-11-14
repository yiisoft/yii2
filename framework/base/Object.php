<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;

/**
 * Object 是实现 *property* 功能的基类。
 *
 * 它已被版本 2.0.13 中的 [[BaseObject]] 取代，因为 `object`
 * 已成为保留字，不能在 PHP 7.2 中用作类名。
 *
 * 请参阅 [[BaseObject]] 获得详细的文档，以及
 * [UPGRADE notes](https://github.com/yiisoft/yii2/blob/2.0.13/framework/UPGRADE.md#upgrade-from-yii-2012)
 * 有关如何迁移应用程序以使用 [[BaseObject]] 类使应用程序与 PHP 7.2 兼容。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 * @deprecated 从 2.0.13 开始，类名 `Object` 自 PHP 7.2 起无效，改为使用 [[BaseObject]]。
 * @see https://wiki.php.net/rfc/object-typehint
 * @see https://github.com/yiisoft/yii2/issues/7936#issuecomment-315384669
 */
class Object extends BaseObject
{
}
