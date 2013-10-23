<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\email\swift;

use yii\email\BaseMessage;

/**
 * Email message based on SwiftMailer library.
 *
 * @see http://swiftmailer.org/docs/messages.html
 * @see \yii\email\swift\Mailer
 *
 * @method \yii\email\swift\Mailer getMailer() returns mailer instance.
 * @property \Swift_Message $swiftMessage vendor message instance.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Message extends BaseMessage
{
	/**
	 * @var \Swift_Message Swift message instance.
	 */
	private $_swiftMessage;

	/**
	 * @return \Swift_Message Swift message instance.
	 */
	public function getSwiftMessage()
	{
		if (!is_object($this->_swiftMessage)) {
			$this->_swiftMessage = $this->getMailer()->createSwiftMessage();
		}
		return $this->_swiftMessage;
	}

	/**
	 * Sets message sender.
	 * @param string|array $from sender email address, if array is given,
	 * its first element should be sender email address, second - sender name.
	 */
	public function setFrom($from)
	{
		if (is_array($from)) {
			list ($address, $name) = $from;
		} else {
			$address = $from;
			$name = null;
		}
		$this->getSwiftMessage()->setFrom($address, $name);
		$this->getSwiftMessage()->setReplyTo($address, $name);
	}

	/**
	 * Sets message receiver.
	 * @param string|array $to receiver email address, if array is given,
	 * its first element should be receiver email address, second - receiver name.
	 */
	public function setTo($to)
	{
		if (is_array($to)) {
			list ($address, $name) = $to;
		} else {
			$address = $to;
			$name = null;
		}
		$this->getSwiftMessage()->setTo($address, $name);
	}

	/**
	 * Sets message subject.
	 * @param string $subject message subject
	 */
	public function setSubject($subject)
	{
		$this->getSwiftMessage()->setSubject($subject);
	}

	/**
	 * Sets message plain text content.
	 * @param string $text message plain text content.
	 */
	public function setText($text)
	{
		$this->getSwiftMessage()->setBody($text, 'text/plain');
	}

	/**
	 * Sets message HTML content.
	 * @param string $html message HTML content.
	 */
	public function setHtml($html)
	{
		$this->getSwiftMessage()->setBody($html, 'text/html');
	}

	/**
	 * Add message plain text content part.
	 * @param string $text message plain text content.
	 */
	public function addText($text)
	{
		$this->getSwiftMessage()->addPart($text, 'text/plain');
	}

	/**
	 * Add message HTML content part.
	 * @param string $html message HTML content.
	 */
	public function addHtml($html)
	{
		$this->getSwiftMessage()->addPart($html, 'text/html');
	}

	/**
	 * Create file attachment for the email message.
	 * @param string $content - attachment file content.
	 * @param string $fileName - attachment file name.
	 * @param string $contentType - MIME type of the attachment file, by default 'application/octet-stream' will be used.
	 */
	public function createAttachment($content, $fileName, $contentType = 'application/octet-stream')
	{
		if (empty($contentType)) {
			$contentType = 'application/octet-stream';
		}
		$attachment = \Swift_Attachment::newInstance($content, $fileName, $contentType);
		$this->getSwiftMessage()->attach($attachment);
	}
}