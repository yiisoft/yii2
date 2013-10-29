<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mail;

use yii\base\InvalidParamException;
use yii\base\Object;
use yii\helpers\FileHelper;
use Yii;

/**
 * BaseMessage represent the single email message.
 * It functionality depends on application component 'email',
 * which should provide the actual email sending functionality as well as
 * default message configuration.
 *
 * @see BaseMailer
 *
 * @property \yii\mail\BaseMailer $mailer mailer component instance. This property is read-only.
 * @property string $charset the character set of this message.
 * @property string|array $from sender email address.
 * @property string|array $to receiver email address.
 * @property string|array $cc copy receiver email address.
 * @property string|array $bcc hidden copy receiver email address.
 * @property string $subject message subject.
 * @property string $text message plain text content.
 * @property string $html message HTML content.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
abstract class BaseMessage extends Object implements MessageInterface
{
	/**
	 * @return \yii\mail\BaseMailer
	 */
	public function getMailer()
	{
		return Yii::$app->getComponent('mail');
	}

	/**
	 * @inheritdoc
	 */
	public function send()
	{
		return $this->getMailer()->send($this);
	}

	/**
	 * @inheritdoc
	 */
	public function render($view, $params = [])
	{
		return $this->getMailer()->render($view, $params);
	}
}