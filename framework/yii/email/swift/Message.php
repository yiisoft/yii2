<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\email\swift;

use yii\email\VendorMessage;

/**
 * Class Message
 *
 * @see http://swiftmailer.org/docs/messages.html
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
	 * @return $this self reference
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
		return $this;
	}

	/**
	 * Sets message receiver.
	 * @param string|array $to receiver email address, if array is given,
	 * its first element should be receiver email address, second - receiver name.
	 * @return $this self reference
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
		return $this;
	}

	/**
	 * Sets message subject.
	 * @param string $subject message subject
	 * @return $this self reference
	 */
	public function setSubject($subject)
	{
		$this->getVendorMessage()->setSubject($subject);
		return $this;
	}

	/**
	 * Sets message plain text content.
	 * @param string $text message plain text content.
	 * @return $this self reference.
	 */
	public function setText($text)
	{
		$this->getVendorMessage()->setBody($text, 'text/plain');
		return $this;
	}

	/**
	 * Sets message HTML content.
	 * @param string $html message HTML content.
	 * @return $this self reference.
	 */
	public function setHtml($html)
	{
		$this->getVendorMessage()->setBody($html, 'text/html');
		return $this;
	}
}