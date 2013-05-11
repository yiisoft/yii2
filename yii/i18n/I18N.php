<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;

/**
 * I18N provides features related with internationalization (I18N) and localization (L10N).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class I18N extends Component
{
	/**
	 * @var array list of [[MessageSource]] configurations or objects. The array keys are message
	 * categories, and the array values are the corresponding [[MessageSource]] objects or the configurations
	 * for creating the [[MessageSource]] objects. The message categories can contain the wildcard '*' at the end
	 * to match multiple categories with the same prefix. For example, 'app\*' matches both 'app\cat1' and 'app\cat2'.
	 *
	 * This property may be modified on the fly by extensions who want to have their own message sources
	 * registered under their own namespaces.
	 *
	 * The category "yii" and "app" are always defined. The former refers to the messages used in the Yii core
	 * framework code, while the latter refers to the default message category for custom application code.
	 * By default, both of these categories use [[PhpMessageSource]] and the corresponding message files are
	 * stored under "@yii/messages" and "@app/messages", respectively.
	 *
	 * You may override the configuration of both categories.
	 */
	public $translations;
	/**
	 * @var string the path or path alias of the file that contains the plural rules.
	 * By default, this refers to a file shipped with the Yii distribution. The file is obtained
	 * by converting from the data file in the CLDR project.
	 *
	 * If the default rule file does not contain the expected rules, you may copy and modify it
	 * for your application, and then configure this property to point to your modified copy.
	 *
	 * @see http://www.unicode.org/cldr/charts/supplemental/language_plural_rules.html
	 */
	public $pluralRuleFile = '@yii/i18n/data/plurals.php';

	/**
	 * Initializes the component by configuring the default message categories.
	 */
	public function init()
	{
		parent::init();
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

	/**
	 * Translates a message to the specified language.
	 * If the first parameter in `$params` is a number and it is indexed by 0, appropriate plural rules
	 * will be applied to the translated message.
	 * @param string $message the message to be translated.
	 * @param array $params the parameters that will be used to replace the corresponding placeholders in the message.
	 * @param string $language the language code (e.g. `en_US`, `en`). If this is null, the current
	 * [[\yii\base\Application::language|application language]] will be used.
	 * @return string the translated message.
	 */
	public function translate($message, $params = array(), $language = null)
	{
		if ($language === null) {
			$language = Yii::$app->language;
		}

		// allow chars for category: word chars, ".", "-", "/", "\"
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
			$message = $this->applyPluralRules($message, $params[0], $language);
			if (!isset($params['{n}'])) {
				$params['{n}'] = $params[0];
			}
			unset($params[0]);
		}

		return empty($params) ? $message : strtr($message, $params);
	}

	/**
	 * Returns the message source for the given category.
	 * @param string $category the category name.
	 * @return MessageSource the message source for the given category.
	 * @throws InvalidConfigException if there is no message source available for the specified category.
	 */
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

	/**
	 * Applies appropriate plural rules to the given message.
	 * @param string $message the message to be applied with plural rules
	 * @param mixed $number the number by which plural rules will be applied
	 * @param string $language the language code that determines which set of plural rules to be applied.
	 * @return string the message that has applied plural rules
	 */
	protected function applyPluralRules($message, $number, $language)
	{
		if (strpos($message, '|') === false) {
			return $message;
		}
		$chunks = explode('|', $message);

		$rules = $this->getPluralRules($language);
		foreach ($rules as $i => $rule) {
			if (isset($chunks[$i]) && $this->evaluate($rule, $number)) {
				return $chunks[$i];
			}
		}
		$n = count($rules);
		return isset($chunks[$n]) ? $chunks[$n] : $chunks[0];
	}

	private $_pluralRules = array(); // language => rule set

	/**
	 * Returns the plural rules for the given language code.
	 * @param string $language the language code (e.g. `en_US`, `en`).
	 * @return array the plural rules
	 * @throws InvalidParamException if the language code is invalid.
	 */
	protected function getPluralRules($language)
	{
		if (isset($this->_pluralRules[$language])) {
			return $this->_pluralRules;
		}
		$allRules = require(Yii::getAlias($this->pluralRuleFile));
		if (isset($allRules[$language])) {
			return $this->_pluralRules[$language] = $allRules[$language];
		} elseif (preg_match('/^[a-z]+/', strtolower($language), $matches)) {
			return $this->_pluralRules[$language] = isset($allRules[$matches[0]]) ? $allRules[$matches[0]] : array();
		} else {
			throw new InvalidParamException("Invalid language code: $language");
		}
	}

	/**
	 * Evaluates a PHP expression with the given number value.
	 * @param string $expression the PHP expression
	 * @param mixed $n the number value
	 * @return boolean the expression result
	 */
	protected function evaluate($expression, $n)
	{
		return eval("return $expression;");
	}
}
