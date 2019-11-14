<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

/**
 * VarDumper 对象用于替换具有 bug 的 PHP 函数 var_dump 和 print_r。
 * 它可以正确识别复杂对象结构中递归引用的对象。
 * 它还可以对递归深度进行控制，
 * 避免出现某些特殊变量的无限递归。
 *
 * VarDumper 用法如下，
 *
 * ```php
 * VarDumper::dump($var);
 * ```
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class VarDumper extends BaseVarDumper
{
}
