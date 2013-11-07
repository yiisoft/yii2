<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mail;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\ViewContextInterface;

/**
 * BaseMailer provides the basic interface for the email mailer application component.
 * It provides the default configuration for the email messages.
 * Particular implementation of mailer should provide implementation for the [[send()]] method.
 *
 * @see BaseMessage
 *
 * @property \yii\base\View|array $view view instance or its array configuration.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
abstract class BaseMailer extends Component implements MailerInterface, ViewContextInterface
{
	/**
	 * @var \yii\base\View|array view instance or its array configuration.
	 */
	private $_view = [];
	/**
	 * @var string directory containing view files for this email messages.
	 */
	public $viewPath = '@app/mails';
	/**
	 * @var string HTML layout view name.
	 */
	public $htmlLayout = 'layouts/html';
	/**
	 * @var string text layout view name.
	 */
	public $textLayout = 'layouts/text';
	/**
	 * @var array configuration, which should be applied by default to any new created
	 * email message instance.
	 * In addition to normal [[Yii::createObject()]] behavior extra config keys are available:
	 *
	 *  - 'charset' argument for [[MessageInterface::charset()]]
	 *  - 'from' argument for [[MessageInterface::from()]]
	 *  - 'to' argument for [[MessageInterface::to()]]
	 *  - 'cc' argument for [[MessageInterface::cc()]]
	 *  - 'bcc' argument for [[MessageInterface::bcc()]]
	 *  - 'subject' argument for [[MessageInterface::subject()]]
	 *  - 'textBody' argument for [[MessageInterface::textBody()]]
	 *  - 'htmlBody' argument for [[MessageInterface::htmlBody()]]
	 *
	 * For example:
	 *
	 * ~~~
	 * array(
	 *     'charset' => 'UTF-8',
	 *     'from' => 'noreply@mydomain.com',
	 *     'bcc' => 'developer@mydomain.com',
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
	 * Creates view instance from given configuration.
	 * @param array $config view configuration.
	 * @return \yii\base\View view instance.
	 */
	protected function createView(array $config)
	{
		if (!array_key_exists('class', $config)) {
			$config['class'] = 'yii\web\View';
		}
		return Yii::createObject($config);
	}

	/**
	 * @inheritdoc
	 */
	public function compose($view = null, array $params = [])
	{
		$message = $this->createMessage();
		if ($view !== null) {
			$params['message'] = $message;
			if (is_array($view)) {
				if (array_key_exists('html', $view)) {
					$message->htmlBody($this->render($view['html'], $params, $this->htmlLayout));
				}
				if (array_key_exists('text', $view)) {
					$message->textBody($this->render($view['text'], $params, $this->textLayout));
				}
			} else {
				$html = $this->render($view, $params, $this->htmlLayout);
				$message->htmlBody($html);
				$message->textBody(strip_tags($html));
			}
		}
		return $message;
	}

	/**
	 * Creates mew message instance using configuration from [[messageConfig]].
	 * If 'class' parameter is omitted, [[messageClass]] will be used.
	 * @return MessageInterface message instance.
	 */
	protected function createMessage()
	{
		$config = $this->messageConfig;
		if (!array_key_exists('class', $config)) {
			$config['class'] = $this->messageClass;
		}
		$directSetterNames = [
			'charset',
			'from',
			'to',
			'cc',
			'bcc',
			'subject',
			'textBody',
			'htmlBody',
		];
		$directSetterConfig = [];
		foreach ($config as $name => $value) {
			if (in_array($name, $directSetterNames, true)) {
				$directSetterConfig[$name] = $value;
				unset($config[$name]);
			}
		}
		$message = Yii::createObject($config);
		foreach ($directSetterConfig as $name => $value) {
			$message->$name($value);
		}
		return $message;
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
	 * @param string|boolean $layout layout view name, if false given no layout will be applied.
	 * @return string the rendering result.
	 */
	public function render($view, $params = [], $layout = false)
	{
		$output = $this->getView()->render($view, $params, $this);
		if ($layout !== false) {
			return $this->getView()->render($layout, ['content' => $output], $this);
		} else {
			return $output;
		}
	}

	/**
	 * Finds the view file corresponding to the specified relative view name.
	 * @param string $view a relative view name. The name does NOT start with a slash.
	 * @return string the view file path. Note that the file may not exist.
	 */
	public function findViewFile($view)
	{
		return Yii::getAlias($this->viewPath) . DIRECTORY_SEPARATOR . $view;
	}
}
