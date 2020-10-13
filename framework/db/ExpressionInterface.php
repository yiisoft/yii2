<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * Interface ExpressionInterface should be used to mark classes, that should be built
 * in a special way.
 *
 * The database abstraction layer of Yii framework supports objects that implement this
 * interface and will use [[ExpressionBuilderInterface]] to build them.
 *
 * The default implementation is a class [[Expression]].
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
interface ExpressionInterface
{
}
