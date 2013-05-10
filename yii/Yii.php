<?php
/**
 * Yii bootstrap file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

require(__DIR__ . '/YiiBase.php');

/**
 * Yii is a helper class serving common framework functionalities.
 *
 * It extends from [[YiiBase]] which provides the actual implementation.
 * By writing your own Yii class, you can customize some functionalities of [[YiiBase]].
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Yii extends \yii\YiiBase
{
}

spl_autoload_register(array('Yii', 'autoload'));
