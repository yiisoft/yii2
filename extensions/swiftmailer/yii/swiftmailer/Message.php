<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\swiftmailer;

use yii\mail\BaseMessage;

/**
 * Email message based on SwiftMailer library.
 *
 * @see http://swiftmailer.org/docs/messages.html
 * @see \yii\swiftmailer\Mailer
 *
 * @method Mailer getMailer() returns mailer instance.
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
	 * @inheritdoc
	 */
	public function setFrom($from)
	{
		$this->getSwiftMessage()->setFrom($from);
		$this->getSwiftMessage()->setReplyTo($from);
	}

	/**
	 * @return string from address of this message.
	 */
	public function getFrom()
	{
		return $this->getSwiftMessage()->getFrom();
	}

	/**
	 * @inheritdoc
	 */
	public function setTo($to)
	{
		$this->getSwiftMessage()->setTo($to);
	}

	/**
	 * @return array To addresses of this message.
	 */
	public function getTo()
	{
		return $this->getSwiftMessage()->getTo();
	}

	/**
	 * @inheritdoc
	 */
	public function setCc($cc)
	{
		$this->getSwiftMessage()->setCc($cc);
	}

	/**
	 * @return array Cc address of this message.
	 */
	public function getCc()
	{
		return $this->getSwiftMessage()->getCc();
	}

	/**
	 * @inheritdoc
	 */
	public function setBcc($bcc)
	{
		$this->getSwiftMessage()->setBcc($bcc);
	}

	/**
	 * @return array Bcc addresses of this message.
	 */
	public function getBcc()
	{
		return $this->getSwiftMessage()->getBcc();
	}

	/**
	 * @inheritdoc
	 */
	public function setSubject($subject)
	{
		$this->getSwiftMessage()->setSubject($subject);
	}

	/**
	 * @return string the subject of this message.
	 */
	public function getSubject()
	{
		return $this->getSwiftMessage()->getSubject();
	}

	/**
	 * @inheritdoc
	 */
	public function setText($text)
	{
		$this->getSwiftMessage()->setBody($text, 'text/plain');
	}

	/**
	 * @inheritdoc
	 */
	public function setHtml($html)
	{
		$this->getSwiftMessage()->setBody($html, 'text/html');
	}

	/**
	 * @inheritdoc
	 */
	public function addText($text)
	{
		$this->getSwiftMessage()->addPart($text, 'text/plain');
	}

	/**
	 * @inheritdoc
	 */
	public function addHtml($html)
	{
		$this->getSwiftMessage()->addPart($html, 'text/html');
	}

	/**
	 * @inheritdoc
	 */
	public function attachContentAsFile($content, $fileName, $contentType = 'application/octet-stream')
	{
		if (empty($contentType)) {
			$contentType = 'application/octet-stream';
		}
		$attachment = \Swift_Attachment::newInstance($content, $fileName, $contentType);
		$this->getSwiftMessage()->attach($attachment);
	}

	/**
	 * @inheritdoc
	 */
	public function __toString()
	{
		return $this->getSwiftMessage()->toString();
	}
}