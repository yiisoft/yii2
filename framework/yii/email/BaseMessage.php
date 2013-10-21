<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\email;

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
 * @property \yii\email\BaseMailer $mailer mailer component instance. This property is read-only.
 * @property string|array $from sender email address, if array is given, its first element should
 * be sender email address, second - sender name.
 * @property string|array $to receiver email address, if array is given, its first element should
 * be receiver email address, second - receiver name.
 * @property string $subject message subject.
 * @property string $text message plain text content.
 * @property string $html message HTML content.
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
	 * Initializes the object.
	 * This method is invoked at the end of the constructor after the object is initialized with the
	 * given configuration.
	 */
	public function init()
	{
		Yii::configure($this, $this->getMailer()->getDefaultMessageConfig());
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
	 */
	abstract public function setFrom($from);

	/**
	 * Sets message receiver.
	 * @param string|array $to receiver email address, if array is given,
	 * its first element should be receiver email address, second - receiver name.
	 */
	abstract public function setTo($to);

	/**
	 * Sets message subject.
	 * @param string $subject message subject
	 */
	abstract public function setSubject($subject);

	/**
	 * Sets message plain text content.
	 * @param string $text message plain text content.
	 */
	abstract public function setText($text);

	/**
	 * Sets message HTML content.
	 * @param string $html message HTML content.
	 */
	abstract public function setHtml($html);

	/**
	 * Create file attachment for the email message.
	 * @param string $content attachment file content.
	 * @param string $fileName attachment file name.
	 * @param string $contentType MIME type of the attachment file, by default 'application/octet-stream' will be used.
	 */
	abstract public function createAttachment($content, $fileName, $contentType = 'application/octet-stream');

	/**
	 * Attaches existing file to the email message.
	 * @param string $fileName full file name
	 * @param string $contentType MIME type of the attachment file, if empty it will be suggested automatically.
	 * @param string $attachFileName name, which should be used for attachment, if empty file base name will be used.
	 * @throws \yii\base\InvalidParamException if given file does not exist.
	 */
	public function attachFile($fileName, $contentType = null, $attachFileName = null)
	{
		if (!file_exists($fileName)) {
			throw new InvalidParamException('Unable to attach file "' . $fileName . '": file does not exists!');
		}
		if (empty($contentType)) {
			$contentType = FileHelper::getMimeType($fileName);
		}
		if (empty($attachFileName)) {
			$attachFileName = basename($fileName);
		}
		$content = file_get_contents($fileName);
		$this->createAttachment($content, $attachFileName, $contentType);
	}
}