<?php
/**
 * BadUsageException class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console;

/**
 * BadUsageException represents an exception caused by incorrect usage of the end user.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class BadUsageException extends \yii\base\Exception
{
	/**
	 * @var boolean whether this exception is caused by end user's mistake (e.g. wrong URL)
	 */
	public $causedByUser = true;

	/**
	 * @return string the user-friendly name of this exception
	 */
	public function getName()
	{
		return \Yii::t('yii', 'Bad Usage');
	}
}

