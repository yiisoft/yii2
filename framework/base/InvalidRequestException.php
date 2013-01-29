<?php
/**
 * InvalidRequestException class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * InvalidRequestException represents an exception caused by incorrect end user request.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class InvalidRequestException extends \Exception
{
	/**
	 * @var string the user-friend name of this exception
	 */
	public $name = 'Invalid Request Exception';
}

