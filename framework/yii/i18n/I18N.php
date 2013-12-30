<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

use Yii;
use yii\base\Arrayable;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * I18N provides features related with internationalization (I18N) and localization (L10N).
 *
 * I18N is configured as an application component in [[yii\base\Application]] by default.
 * You can access that instance via `Yii::$app->i18n`.
 *
 * @property MessageFormatter $messageFormatter The message formatter to be used to format message via ICU
 * message format. Note that the type of this property differs in getter and setter. See
 * [[getMessageFormatter()]] and [[setMessageFormatter()]] for details.
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
	 * Initializes the component by configuring the default message categories.
	 */
	public function init()
	{
		parent::init();
		if (!isset($this->translations['yii'])) {
			$this->translations['yii'] = [
				'class' => 'yii\i18n\PhpMessageSource',
				'sourceLanguage' => 'en-US',
				'basePath' => '@yii/messages',
			];
		}
		if (!isset($this->translations['app'])) {
			$this->translations['app'] = [
				'class' => 'yii\i18n\PhpMessageSource',
				'sourceLanguage' => 'en-US',
				'basePath' => '@app/messages',
			];
		}
	}

	/**
	 * Translates a message to the specified language.
	 *
	 * After translation the message will be formatted using [[MessageFormatter]] if it contains
	 * ICU message format and `$params` are not empty.
	 *
	 * @param string $category the message category.
	 * @param string $message the message to be translated.
	 * @param array $params the parameters that will be used to replace the corresponding placeholders in the message.
	 * @param string $language the language code (e.g. `en-US`, `en`).
	 * @return string the translated and formatted message.
	 */
	public function translate($category, $message, $params, $language)
	{
		$message = $this->getMessageSource($category)->translate($category, $message, $language);
		return $this->format($message, $params, $language);
	}

	/**
	 * Formats a message using [[MessageFormatter]].
	 *
	 * @param string $message the message to be formatted.
	 * @param array $params the parameters that will be used to replace the corresponding placeholders in the message.
	 * @param string $language the language code (e.g. `en-US`, `en`).
	 * @return string the formatted message.
	 */
	public function format($message, $params, $language)
	{
		if ($params instanceof Arrayable) {
			$params = $params->toArray();
		} else {
			$params = (array)$params;
		}
		if ($params === []) {
			return $message;
		}

		if (preg_match('~{\s*[\d\w]+\s*,~u', $message)) {
			$formatter = $this->getMessageFormatter();
			$result = $formatter->format($message, $params, $language);
			if ($result === false) {
				$errorMessage = $formatter->getErrorMessage();
				Yii::warning("Formatting message for language '$language' failed with error: $errorMessage. The message being formatted was: $message.");
				return $message;
			} else {
				return $result;
			}
		}

		$p = [];
		foreach($params as $name => $value) {
			$p['{' . $name . '}'] = $value;
		}
		return strtr($message, $p);
	}

	/**
	 * @var string|array|MessageFormatter
	 */
	private $_messageFormatter;

	/**
	 * Returns the message formatter instance.
	 * @return MessageFormatter the message formatter to be used to format message via ICU message format.
	 */
	public function getMessageFormatter()
	{
		if ($this->_messageFormatter === null) {
			$this->_messageFormatter = new MessageFormatter();
		} elseif (is_array($this->_messageFormatter) || is_string($this->_messageFormatter)) {
			$this->_messageFormatter = Yii::createObject($this->_messageFormatter);
		}
		return $this->_messageFormatter;
	}

	/**
	 * @param string|array|MessageFormatter $value the message formatter to be used to format message via ICU message format.
	 * Can be given as array or string configuration that will be given to [[Yii::createObject]] to create an instance
	 * or a [[MessageFormatter]] instance.
	 */
	public function setMessageFormatter($value)
	{
		$this->_messageFormatter = $value;
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
			if ($source instanceof MessageSource) {
				return $source;
			} else {
				return $this->translations[$category] = Yii::createObject($source);
			}
		} else {
			// try wildcard matching
			foreach ($this->translations as $pattern => $config) {
				if ($pattern === '*' || substr($pattern, -1) === '*' && strpos($category, rtrim($pattern, '*')) === 0) {
					if ($config instanceof MessageSource) {
						return $config;
					} else {
						return $this->translations[$category] = $this->translations[$pattern] = Yii::createObject($config);
					}
				}
			}
		}

		throw new InvalidConfigException("Unable to locate message source for category '$category'.");
	}
}
