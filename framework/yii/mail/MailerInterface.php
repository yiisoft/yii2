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
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
interface MailerInterface
{
	/**
	 * Creates new message instance from given configuration.
	 * @param array $config message configuration.
	 * @return MessageInterface message instance.
	 */
	public function createMessage(array $config = []);

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