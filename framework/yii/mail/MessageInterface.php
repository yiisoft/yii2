<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mail;

/**
 * Class MessageInterface
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
interface MessageInterface
{
	/**
	 * Sets message sender.
	 * @param string|array $from sender email address.
	 * You may pass an array of addresses if this message is from multiple people.
	 * You may also specify sender name in addition to email address using format:
	 * [email => name].
	 */
	public function setFrom($from);

	/**
	 * Sets message receiver.
	 * @param string|array $to receiver email address.
	 * You may pass an array of addresses if multiple recipients should receive this message.
	 * You may also specify receiver name in addition to email address using format:
	 * [email => name].
	 */
	public function setTo($to);

	/**
	 * Set the Cc (additional copy receiver) addresses of this message.
	 * @param string|array $cc copy receiver email address.
	 * You may pass an array of addresses if multiple recipients should receive this message.
	 * You may also specify receiver name in addition to email address using format:
	 * [email => name].
	 */
	public function setCc($cc);

	/**
	 * Set the Bcc (hidden copy receiver) addresses of this message.
	 * @param string|array $bcc hidden copy receiver email address.
	 * You may pass an array of addresses if multiple recipients should receive this message.
	 * You may also specify receiver name in addition to email address using format:
	 * [email => name].
	 */
	public function setBcc($bcc);

	/**
	 * Sets message subject.
	 * @param string $subject message subject
	 */
	public function setSubject($subject);

	/**
	 * Sets message plain text content.
	 * @param string $text message plain text content.
	 */
	public function setText($text);

	/**
	 * Sets message HTML content.
	 * @param string $html message HTML content.
	 */
	public function setHtml($html);

	/**
	 * Add message plain text content part.
	 * @param string $text message plain text content.
	 */
	public function addText($text);

	/**
	 * Add message HTML content part.
	 * @param string $html message HTML content.
	 */
	public function addHtml($html);

	/**
	 * Create file attachment for the email message.
	 * @param string $content attachment file content.
	 * @param string $fileName attachment file name.
	 * @param string $contentType MIME type of the attachment file, by default 'application/octet-stream' will be used.
	 */
	public function createAttachment($content, $fileName, $contentType = 'application/octet-stream');

	/**
	 * Attaches existing file to the email message.
	 * @param string $fileName full file name
	 * @param string $contentType MIME type of the attachment file, if empty it will be suggested automatically.
	 * @param string $attachFileName name, which should be used for attachment, if empty file base name will be used.
	 */
	public function attachFile($fileName, $contentType = null, $attachFileName = null);

	/**
	 * Sends this email message.
	 * @return boolean success.
	 */
	public function send();

	/**
	 * Renders a view.
	 * The view to be rendered can be specified in one of the following formats:
	 * - path alias (e.g. "@app/emails/contact/body");
	 * - relative path (e.g. "contact"): the actual view file will be resolved by [[resolveView]].
	 * @param string $view the view name or the path alias of the view file.
	 * @param array $params the parameters (name-value pairs) that will be extracted and made available in the view file.
	 * @return string string the rendering result
	 */
	public function render($view, $params = []);
}