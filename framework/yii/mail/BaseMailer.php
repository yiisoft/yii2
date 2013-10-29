<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mail;

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
 * @property \yii\base\View|array $view view instance or its array configuration.
 * @property \yii\mail\ViewResolver|array $viewResolver view resolver instance or its array configuration.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
abstract class BaseMailer extends Component implements MailerInterface
{
	/**
	 * @var \yii\base\View|array view instance or its array configuration.
	 */
	private $_view = [];
	/**
	 * @var \yii\mail\ViewResolver|array view resolver instance or its array configuration.
	 */
	private $_viewResolver = [];
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
	public $messageConfig = [];
	/**
	 * @var string message default class name.
	 */
	public $messageClass = 'yii\mail\BaseMessage';

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
	 * @param array|\yii\mail\ViewResolver $viewResolver view resolver instance or its array configuration.
	 * @throws \yii\base\InvalidConfigException on invalid argument.
	 */
	public function setViewResolver($viewResolver)
	{
		if (!is_array($viewResolver) && !is_object($viewResolver)) {
			throw new InvalidConfigException('"' . get_class($this) . '::viewResolver" should be either object or array, "' . gettype($viewResolver) . '" given.');
		}
		$this->_viewResolver = $viewResolver;
	}

	/**
	 * @return \yii\mail\ViewResolver view resolver.
	 */
	public function getViewResolver()
	{
		if (!is_object($this->_viewResolver)) {
			$this->_viewResolver = $this->createViewResolver($this->_viewResolver);
		}
		return $this->_viewResolver;
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
		return Yii::createObject($config);
	}

	/**
	 * Creates view resolver instance from given configuration.
	 * @param array $config view resolver configuration.
	 * @return \yii\mail\ViewResolver view resolver instance.
	 */
	protected function createViewResolver(array $config)
	{
		if (!array_key_exists('class', $config)) {
			$config['class'] = '\yii\mail\ViewResolver';
		}
		return Yii::createObject($config);
	}

	/**
	 * Creates new message instance from given configuration.
	 * Message configuration will be merged with [[messageConfig]].
	 * If 'class' parameter is omitted [[messageClass]], will be used.
	 * @param array $config message configuration.
	 * @return MessageInterface message instance.
	 */
	public function createMessage(array $config = [])
	{
		$config = array_merge($this->messageConfig, $config);
		if (!array_key_exists('class', $config)) {
			$config['class'] = $this->messageClass;
		}
		return Yii::createObject($config);
	}

	/**
	 * Sends a couple of messages at once.
	 * Note: some particular mailers may benefit from sending messages as batch,
	 * saving resources, for example on open/close connection operations,
	 * they may override this method to create their specific implementation.
	 * @param array $messages list of email messages, which should be sent.
	 * @return integer number of successful sends.
	 */
	public function sendMultiple(array $messages)
	{
		$successCount = 0;
		foreach ($messages as $message) {
			if ($this->send($message)) {
				$successCount++;
			}
		}
		return $successCount;
	}

	/**
	 * Renders a view.
	 * @param string $view the view name or the path alias of the view file.
	 * @param array $params the parameters (name-value pairs) that will be extracted and made available in the view file.
	 * @return string string the rendering result
	 */
	public function render($view, $params = [])
	{
		return $this->getView()->renderFile($this->getViewResolver()->findViewFile($view), $params, $this);
	}
}