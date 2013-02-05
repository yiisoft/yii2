<?php

namespace yii\i18n;

use Yii;
use yii\base\Component;

class I18N extends Component
{
	public function translate($message, $params = array(), $language = null)
	{
		if ($language === null) {
			$language = Yii::$app->language;
		}

		if (strpos($message, '|') !== false && preg_match('/^([\w\-\.]+)\|(.*)/', $message, $matches)) {
			$category = $matches[1];
			$message = $matches[2];
		} else {
			$category = 'app';
		}

		$message = $this->getMessageSource($category)->translate($category, $message, $language);

		if (!is_array($params)) {
			$params = array($params);
		}

		if (isset($params[0])) {
			$message = $this->getPluralFormat($message, $params[0], $language);
			if (!isset($params['{n}'])) {
				$params['{n}'] = $params[0];
			}
			unset($params[0]);
		}

		return $params === array() ? $message : strtr($message, $params);
	}

	public function getLocale($language)
	{

	}

	public function getMessageSource($category)
	{
		return $category === 'yii' ? $this->getMessages() : $this->getCoreMessages();
	}

	private $_coreMessages;
	private $_messages;

	public function getCoreMessages()
	{
		if (is_object($this->_coreMessages)) {
			return $this->_coreMessages;
		} elseif ($this->_coreMessages === null) {
			return $this->_coreMessages = new PhpMessageSource(array(
				'sourceLanguage' => 'en_US',
				'basePath' => '@yii/messages',
			));
		} else {
			return $this->_coreMessages = Yii::createObject($this->_coreMessages);
		}
	}

	public function setCoreMessages($config)
	{
		$this->_coreMessages = $config;
	}

	public function getMessages()
	{
		if (is_object($this->_messages)) {
			return $this->_messages;
		} elseif ($this->_messages === null) {
			return $this->_messages = new PhpMessageSource(array(
				'sourceLanguage' => 'en_US',
				'basePath' => '@app/messages',
			));
		} else {
			return $this->_messages = Yii::createObject($this->_messages);
		}
	}

	public function setMessages($config)
	{
		$this->_messages = $config;
	}

	protected function getPluralFormat($message, $number, $language)
	{
		if (strpos($message, '|') === false) {
			return $message;
		}
		$chunks = explode('|', $message);
		$rules = $this->getLocale($language)->getPluralRules();
		foreach ($rules as $i => $rule) {
			if (isset($chunks[$i]) && self::evaluate($rule, $number)) {
				return $chunks[$i];
			}
		}
		$n = count($rules);
		return isset($chunks[$n]) ? $chunks[$n] : $chunks[0];
	}

	/**
	 * Evaluates a PHP expression with the given number value.
	 * @param string $expression the PHP expression
	 * @param mixed $n the number value
	 * @return boolean the expression result
	 */
	protected static function evaluate($expression, $n)
	{
		return @eval("return $expression;");
	}
}
