<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\email;

use yii\base\Object;
use Yii;

/**
 * Class BaseMessage
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
abstract class BaseMessage extends Object
{
	/**
	 * @return \yii\email\BaseMailer
	 */
	public function getMailer()
	{
		return Yii::$app->getComponent('email');
	}

	/**
	 * Sends this email message.
	 * @return boolean success.
	 */
	public function send()
	{
		return $this->getMailer()->send($this);
	}
}