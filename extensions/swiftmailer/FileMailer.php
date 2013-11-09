<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\swiftmailer;

use yii\helpers\FileHelper;
use yii\mail\BaseMailer;
use Yii;

/**
 * FileMailer implements a mailer that saves mails as files under runtime directory.
 *
 * To use FileMailer, you should configure it in the application configuration like the following,
 *
 * ~~~
 * 'components' => array(
 *     ...
 *     'email' => array(
 *         'class' => 'yii\swiftmailer\FileMailer'
 *     ),
 *     ...
 * ),
 * ~~~
 *
 * To send an email, you may use the following code:
 *
 * ~~~
 * Yii::$app->mail->compose('contact/html', ['contactForm' => $form])
 *     ->setFrom('from@domain.com')
 *     ->setTo($form->email)
 *     ->setSubject($form->subject)
 *     ->send();
 * ~~~
 *
 * @author Dmitry Erofeev <dmeroff@gmail.com>
 * @since 2.0
 */
class FileMailer extends BaseMailer
{
	/**
	 * @var string message default class name.
	 */
	public $messageClass = 'yii\swiftmailer\Message';

	/**
	 * @var string The path under which mail files will be written. Defaults to "@app/runtime/mail".
	 */
	protected $path;

	/**
	 * @var callable Callback that will be used to generate name of the message to save.
	 */
	protected $callback;

	/**
	 * Sets path under which mail files will be written.
	 * Any trailing '/' and '\' characters in the given path will be trimmed.
	 *
	 * @param $path
	 * @throws \InvalidArgumentException
	 */
	public function setPath($path)
	{
		$path = Yii::getAlias($path);
		if (!is_dir($path) || !is_writable($path)) {
			throw new \InvalidArgumentException('Filemailer::setPath expects a valid path in which to write mail files');
		}

		$this->path = rtrim($path, '\\/');
	}

	/**
	 * Gets path under which mail files will be written.
	 *
	 * @return string
	 */
	public function getPath()
	{
		if ($this->path == null) {
			$path = Yii::getAlias('@app/runtime/mail');
			if (!is_dir($path)) {
				FileHelper::createDirectory($path);
			}
			$this->setPath($path);
		}

		return $this->path;
	}

	/**
	 * Sets callback that will be used to generate name of the message to save.
	 *
	 * @param callable $callback
	 */
	public function setCallback(callable $callback)
	{
		$this->callback = $callback;
	}

	/**
	 * Gets callback that will be used to generate name of the message to save.
	 *
	 * @return callable
	 */
	public function getCallback()
	{
		if ($this->callback == null) {
			$this->setCallback(function () {
				return uniqid('Message_') . '.txt';
			});
		}

		return $this->callback;
	}

	/**
	 * @inheritdoc
	 */
	public function send($message)
	{
		$address = $message->getTo();
		if (is_array($address)) {
			$address = implode(', ', array_keys($address));
		}
		Yii::trace('Sending email "' . $message->getSubject() . '" to "' . $address . '"', __METHOD__);

		$filename = $this->getPath() . DIRECTORY_SEPARATOR . call_user_func($this->getCallback());

		return file_put_contents($filename, $message->toString()) !== false;
	}
}