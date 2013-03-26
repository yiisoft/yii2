<?php

namespace yii\i18n;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

class I18N extends Component
{
	/**
	 * @var array list of [[MessageSource]] configurations or objects. The array keys are message
	 * categories, and the array values are the corresponding [[MessageSource]] objects or the configurations
	 * for creating the [[MessageSource]] objects. The message categories can contain the wildcard '*' at the end
	 * to match multiple categories with the same prefix. For example, 'app\*' matches both 'app\cat1' and 'app\cat2'.
	 */
	public $translations;

	public function init()
	{
		if (!isset($this->translations['yii'])) {
			$this->translations['yii'] = array(
				'class' => 'yii\i18n\PhpMessageSource',
				'sourceLanguage' => 'en_US',
				'basePath' => '@yii/messages',
			);
		}
		if (!isset($this->translations['app'])) {
			$this->translations['app'] = array(
				'class' => 'yii\i18n\PhpMessageSource',
				'sourceLanguage' => 'en_US',
				'basePath' => '@app/messages',
			);
		}
	}

	public function translate($message, $params = array(), $language = null)
	{
		if ($language === null) {
			$language = Yii::$app->language;
		}

		// allow chars for category: word chars, ".", "-", "/","\"
		if (strpos($message, '|') !== false && preg_match('/^([\w\-\\/\.\\\\]+)\|(.*)/', $message, $matches)) {
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
			$message = $this->getPluralForm($message, $params[0], $language);
			if (!isset($params['{n}'])) {
				$params['{n}'] = $params[0];
			}
			unset($params[0]);
		}

		return $params === array() ? $message : strtr($message, $params);
	}

	public function getMessageSource($category)
	{
		if (isset($this->translations[$category])) {
			$source = $this->translations[$category];
		} else {
			// try wildcard matching
			foreach ($this->translations as $pattern => $config) {
				if (substr($pattern, -1) === '*' && strpos($category, rtrim($pattern, '*')) === 0) {
					$source = $config;
					break;
				}
			}
		}
		if (isset($source)) {
			return $source instanceof MessageSource ? $source : Yii::createObject($source);
		} else {
			throw new InvalidConfigException("Unable to locate message source for category '$category'.");
		}
	}

	public function getLocale($language)
	{

	}

	protected function getPluralForm($message, $number, $language)
	{
		if (strpos($message, '|') === false) {
			return $message;
		}
		$chunks = explode('|', $message);
		$rules = $this->getLocale($language)->getPluralRules();
		foreach ($rules as $i => $rule) {
			if (isset($chunks[$i]) && $this->evaluate($rule, $number)) {
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
	protected function evaluate($expression, $n)
	{
		return @eval("return $expression;");
	}
}
