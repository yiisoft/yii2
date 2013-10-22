<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\email;

use yii\base\Component;
use yii\base\InvalidConfigException;
use Yii;

/**
 * BaseMailer provides the basic interface for the email mailer application component.
 * It provides the default configuration for the email messages.
 * Particular implementation of mailer should provide implementation for the [[send()]] method.
 *
 * @see BaseMessage
 *
 * @property array $view view instance or its array configuration.
 * @property array $defaultMessageConfig configuration, which should be applied by default to any
 * new created email message instance.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
abstract class BaseMailer extends Component
{
	/**
	 * @var \yii\base\View|array view instance or its array configuration.
	 */
	private $_view = [];
	/**
	 * @var array configuration, which should be applied by default to any new created
	 * email message instance.
	 * For example:
	 * ~~~
	 * array(
	 *     'encoding' => 'UTF-8',
	 *     'from' => 'noreply@mydomain.com',
	 *     'bcc' => 'email.test@mydomain.com',
	 * )
	 * ~~~
	 */
	private $_defaultMessageConfig = [];

	/**
	 * @param array|\yii\base\View $view view instance or its array configuration.
	 * @throws \yii\base\InvalidConfigException on invalid argument.
	 */
	public function setView($view)
	{
		if (!is_array($view) && !is_object($view)) {
			throw new InvalidConfigException('"' . get_class($this) . '::view" should be either object or array, "' . gettype($view) . '" given.');
		}
		$this->_view = $view;
	}

	/**
	 * @return \yii\base\View view instance.
	 */
	public function getView()
	{
		if (!is_object($this->_view)) {
			$this->_view = $this->createView($this->_view);
		}
		return $this->_view;
	}

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
	 * Creates view instance from given configuration.
	 * @param array $config view configuration.
	 * @return \yii\base\View view instance.
	 */
	protected function createView(array $config)
	{
		if (!array_key_exists('class', $config)) {
			$config['class'] = '\yii\base\View';
		}
		$config['context'] = $this;
		return Yii::createObject($config);
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