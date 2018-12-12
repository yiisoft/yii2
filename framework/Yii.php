<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

require __DIR__ . '/BaseYii.php';

/**
 * Yii 是一个服务于通用框架功能的助手类。
 *
 * 它从 [[\yii\BaseYii]] 扩展而来，它提供了实际的实现。
 * 通过编写自己的 Yii 类，您可以自定义 [[\yii\BaseYii]] 的一些功能。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Yii extends \yii\BaseYii
{
}

spl_autoload_register(['Yii', 'autoload'], true, true);
Yii::$classMap = require __DIR__ . '/classes.php';
Yii::$container = new yii\di\Container();
