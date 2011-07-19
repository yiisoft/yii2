<?php
/**
 * Yii bootstrap file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @version $Id: yii.php 2799 2011-01-01 19:31:13Z qiang.xue $
 * @package yii
 * @since 2.0
 */

require(__DIR__ . '/base/YiiBase.php');

/**
 * Yii is a helper class serving common framework functionalities.
 *
 * It encapsulates {@link YiiBase} which provides the actual implementation.
 * By writing your own Yii class, you can customize some functionalities of YiiBase.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: yii.php 2799 2011-01-01 19:31:13Z qiang.xue $
 * @package system
 * @since 2.0
 */
class Yii extends YiiBase
{
}

spl_autoload_register(array('Yii', 'autoload'));
