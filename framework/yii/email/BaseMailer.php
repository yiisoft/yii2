<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\email;

use yii\base\Component;

/**
 * BaseMailer provides the basic interface for the email mailer application component.
 * It provides the default configuration for the email messages.
 * Particular implementation of mailer should provide implementation for the [[send()]] method.
 *
 * @see BaseMessage
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
abstract class BaseMailer extends Component
{
	/**
	 * @var array configuration, which should be applied by default to any new created
	 * email message instance.
	 * For example:
	 * ~~~
	 * array(
	 *     'encoding' => 'UTF-8',
	 *     'from' => 'noreply@mydomain.com',
	 * )
	 * ~~~
	 */
	private $_defaultMessageConfig = array();

	/**
	 * @param array $defaultMessageConfig default message config
	 */
	public function setDefaultMessageConfig(array $defaultMessageConfig)
	{
		$this->_defaultMessageConfig = $defaultMessageConfig;
	}

	/**
	 * @return array default message config
	 */
	public function getDefaultMessageConfig()
	{
		return $this->_defaultMessageConfig;
	}

	/**
	 * Sends the given email message.
	 * @param object $message email message instance
	 * @return boolean whether the message has been sent.
	 */
	abstract public function send($message);

	/**
	 * Sends a couple of messages at once.
	 * Note: some particular mailers may benefit from sending messages as batch,
	 * saving resources, for example on open/close connection operations,
	 * they may override this method to create their specific implementation.
	 * @param array $messages list of email messages, which should be sent.
	 * @return integer number of successfull sends
	 */
	public function sendMultiple(array $messages) {
		$successCount = 0;
		foreach ($messages as $message) {
			if ($this->send($message)) {
				$successCount++;
			}
		}
		return $successCount;
	}
}