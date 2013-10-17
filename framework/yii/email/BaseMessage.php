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

	/**
	 * Sets message sender.
	 * @param string|array $from sender email address, if array is given,
	 * its first element should be sender email address, second - sender name.
	 * @return $this self reference
	 */
	abstract public function setFrom($from);

	/**
	 * Sets message receiver.
	 * @param string|array $to receiver email address, if array is given,
	 * its first element should be receiver email address, second - receiver name.
	 * @return $this self reference
	 */
	abstract public function setTo($to);

	/**
	 * Sets message subject.
	 * @param string $subject message subject
	 * @return $this self reference
	 */
	abstract public function setSubject($subject);

	/**
	 * Sets message plain text content.
	 * @param string $text message plain text content.
	 * @return $this self reference.
	 */
	abstract public function setText($text);

	/**
	 * Sets message HTML content.
	 * @param string $html message HTML content.
	 * @return $this self reference.
	 */
	abstract public function setHtml($html);
}