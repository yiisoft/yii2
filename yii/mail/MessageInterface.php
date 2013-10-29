<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mail;

/**
 * MessageInterface is an interface, which email message should apply.
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
	 * Attach specified content as file for the email message.
	 * @param string $content attachment file content.
	 * @param array $options options for embed file. Valid options are:
	 * - fileName: name, which should be used to attach file.
	 * - contentType: attached file MIME type.
	 */
	public function attachContent($content, array $options = []);

	/**
	 * Attaches existing file to the email message.
	 * @param string $fileName full file name
	 * @param array $options options for embed file. Valid options are:
	 * - fileName: name, which should be used to attach file.
	 * - contentType: attached file MIME type.
	 */
	public function attachFile($fileName, array $options = []);

	/**
	 * Attach a file and return it's CID source.
	 * This method should be used when embedding images or other data in a message.
	 * @param string $fileName file name.
	 * @param array $options options for embed file. Valid options are:
	 * - fileName: name, which should be used to attach file.
	 * - contentType: attached file MIME type.
	 * @return string attachment CID.
	 */
	public function embedFile($fileName, array $options = []);

	/**
	 * Attach a content as file and return it's CID source.
	 * This method should be used when embedding images or other data in a message.
	 * @param string $content  attachment file content.
	 * @param array $options options for embed file. Valid options are:
	 * - fileName: name, which should be used to attach file.
	 * - contentType: attached file MIME type.
	 * @return string attachment CID.
	 */
	public function embedContent($content, array $options = []);

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

	/**
	 * String output.
	 * This is PHP magic method that returns string representation of an object.
	 * @return string the string representation of the object
	 */
	public function __toString();
}