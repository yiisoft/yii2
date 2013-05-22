<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * InvalidRequestException represents an exception caused by incorrect end user request.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class InvalidRequestException extends UserException
{
	/**
	 * @return string the user-friendly name of this exception
	 */
	public function getName()
	{
		return \Yii::t('yii', 'Invalid Request');
	}
}

