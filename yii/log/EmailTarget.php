<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\log;

use Yii;
use yii\base\InvalidConfigException;
use yii\mail\MailerInterface;

/**
 * EmailTarget sends selected log messages to the specified email addresses.
 *
 * The target email addresses may be specified via [[emails]] property.
 * Optionally, you may set the email [[subject]], [[sentFrom]] address and
 * additional [[headers]].
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class EmailTarget extends Target
{
	/**
	 * @var array the configuration array for creating a [[\yii\mail\MessageInterface|message]] object.
	 * Note that the "to" option must be set, which specifies the destination email address(es).
	 */
	public $message = [];
	/**
	 * @var MailerInterface|string the mailer object or the application component ID of the mailer object.
	 * After the EmailTarget object is created, if you want to change this property, you should only assign it
	 * with a mailer object.
	 */
	public $mail = 'mail';

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		if (empty($this->message['to'])) {
			throw new InvalidConfigException('The "to" option must be set for EmailTarget::message.');
		}
		if (empty($this->message['subject'])) {
			$this->message['subject'] = Yii::t('yii', 'Application Log');
		}
		if (is_string($this->mail)) {
			$this->mail = Yii::$app->getComponent($this->mail);
		}
		if (!$this->mail instanceof MailerInterface) {
			throw new InvalidConfigException("EmailTarget::mailer must be either a mailer object or the application component ID of a mailer object.");
		}
	}

	/**
	 * Sends log messages to specified email addresses.
	 */
	public function export()
	{
		$message = $this->mail->compose();
		Yii::configure($message, $this->message);
		$this->composeMessage($message);
		$this->mail->send($message);
	}

	/**
	 * Composes the given mail message with body content.
	 * The default implementation fills the text body of the message with the log messages.
	 * @param \yii\mail\MessageInterface $message
	 */
	protected function composeMessage($message)
	{
		$messages = array_map([$this, 'formatMessage'], $this->messages);
		$body = wordwrap(implode("\n", $messages), 70);
		$message->setTextBody($body);
	}
}
