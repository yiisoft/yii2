<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * 接口 ExpressionInterface 应用于标记类，
 * 这些类应以特殊方式构建。
 *
 * Yii 框架的数据库抽象层支持实现此接口的对象，
 * 并将使用 [[ExpressionBuilderInterface]] 来构建它们。
 *
 * 默认实现是一个类 [[Expression]]。
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
interface ExpressionInterface
{
}
