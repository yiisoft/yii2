<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mail;

/**
 * MailerInterface is an interface, which any mailer should apply.
 *
 * @see MessageInterface
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
interface MailerInterface
{
	/**
	 * Creates new message optionally filling up its body via view rendering.
	 * The view to be rendered can be specified in one of the following formats:
	 * - path alias (e.g. "@app/mails/contact/body");
	 * - relative path (e.g. "contact"): the actual view file will be resolved by [[\yii\base\ViewContextInterface]].
	 * @param string|array $view view, which should be used to render message body
	 *  - if string - the view name or the path alias of the HTML body view file, in this case
	 * text body will be composed automatically from html one.
	 *  - if array - list of views for each body type in format: ['html' => 'htmlView', 'text' => 'textView']
	 * @param array $params the parameters (name-value pairs) that will be extracted and made available in the view file.
	 * @return MessageInterface message instance.
	 */
	public function compose($view = null, array $params = []);

	/**
	 * Sends the given email message.
	 * @param object $message email message instance
	 * @return boolean whether the message has been sent.
	 */
	public function send($message);

	/**
	 * Sends a couple of messages at once.
	 * Note: some particular mailers may benefit from sending messages as batch,
	 * saving resources, for example on open/close connection operations.
	 * @param array $messages list of email messages, which should be sent.
	 * @return integer number of successful sends.
	 */
	public function sendMultiple(array $messages);
}