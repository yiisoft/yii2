<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

use Yii;
use yii\base\Component;

/**
 * MessageSource is the base class for message translation repository classes.
 *
 * A message source stores message translations in some persistent storage.
 *
 * Child classes should override [[loadMessages()]] to provide translated messages.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class MessageSource extends Component
{
	/**
	 * @event MissingTranslationEvent an event that is triggered when a message translation is not found.
	 */
	const EVENT_MISSING_TRANSLATION = 'missingTranslation';

	/**
	 * @var boolean whether to force message translation when the source and target languages are the same.
	 * Defaults to false, meaning translation is only performed when source and target languages are different.
	 */
	public $forceTranslation = false;
	/**
	 * @var string the language that the original messages are in. If not set, it will use the value of
	 * [[\yii\base\Application::sourceLanguage]].
	 */
	public $sourceLanguage;

	private $_messages = array();

	/**
	 * Initializes this component.
	 */
	public function init()
	{
		parent::init();
		if ($this->sourceLanguage === null) {
			$this->sourceLanguage = Yii::$app->sourceLanguage;
		}
	}

	/**
	 * Loads the message translation for the specified language and category.
	 * Child classes should override this method to return the message translations of
	 * the specified language and category.
	 * @param string $category the message category
	 * @param string $language the target language
	 * @return array the loaded messages. The keys are original messages, and the values
	 * are translated messages.
	 */
	protected function loadMessages($category, $language)
	{
		return array();
	}

	/**
	 * Translates a message to the specified language.
	 *
	 * Note that unless [[forceTranslation]] is true, if the target language
	 * is the same as the [[sourceLanguage|source language]], the message
	 * will NOT be translated.
	 *
	 * If a translation is not found, a [[missingTranslation]] event will be triggered.
	 *
	 * @param string $category the message category
	 * @param string $message the message to be translated
	 * @param string $language the target language
	 * @return string the translated message (or the original message if translation is not needed)
	 */
	public function translate($category, $message, $language)
	{
		if ($this->forceTranslation || $language !== $this->sourceLanguage) {
			return $this->translateMessage($category, $message, $language);
		} else {
			return $message;
		}
	}

	/**
	 * Translates the specified message.
	 * If the message is not found, a [[missingTranslation]] event will be triggered
	 * and the original message will be returned.
	 * @param string $category the category that the message belongs to
	 * @param string $message the message to be translated
	 * @param string $language the target language
	 * @return string the translated message
	 */
	protected function translateMessage($category, $message, $language)
	{
		$key = $language . '/' . $category;
		if (!isset($this->_messages[$key])) {
			$this->_messages[$key] = $this->loadMessages($category, $language);
		}
		if (isset($this->_messages[$key][$message]) && $this->_messages[$key][$message] !== '') {
			return $this->_messages[$key][$message];
		} elseif ($this->hasEventHandlers('missingTranslation')) {
			$event = new MissingTranslationEvent(array(
				'category' => $category,
				'message' => $message,
				'language' => $language,
			));
			$this->trigger(self::EVENT_MISSING_TRANSLATION, $event);
			return $this->_messages[$key] = $event->message;
		} else {
			return $message;
		}
	}
}

