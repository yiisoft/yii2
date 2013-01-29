<?php
/**
 * UnknownMethodException class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * UnknownMethodException represents an exception caused by accessing unknown object methods.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class UnknownMethodException extends \Exception
{
	/**
	 * @var string the user-friend name of this exception
	 */
	public $name = 'Unknown Method Exception';
}

