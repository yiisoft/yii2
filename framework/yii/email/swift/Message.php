<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\email\swift;

use yii\email\VendorMessage;

/**
 * Email message based on SwiftMailer library.
 *
 * @see http://swiftmailer.org/docs/messages.html
 * @see \yii\email\swift\Mailer
 *
 * @method \Swift_Message getVendorMessage() returns vendor message instance.
 * @property \Swift_Message $vendorMessage vendor message instance.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Message extends VendorMessage
{
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
		$this->getVendorMessage()->setFrom($address, $name);
		$this->getVendorMessage()->setReplyTo($address, $name);
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
		$this->getVendorMessage()->setTo($address, $name);
	}

	/**
	 * Sets message subject.
	 * @param string $subject message subject
	 */
	public function setSubject($subject)
	{
		$this->getVendorMessage()->setSubject($subject);
	}

	/**
	 * Sets message plain text content.
	 * @param string $text message plain text content.
	 */
	public function setText($text)
	{
		$this->getVendorMessage()->setBody($text, 'text/plain');
	}

	/**
	 * Sets message HTML content.
	 * @param string $html message HTML content.
	 */
	public function setHtml($html)
	{
		$this->getVendorMessage()->setBody($html, 'text/html');
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
		$this->getVendorMessage()->attach($attachment);
	}
}