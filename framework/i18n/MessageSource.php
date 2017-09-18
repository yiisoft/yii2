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
abstract class MessageSource extends Component
{
    /**
     * @event MissingTranslationEvent an event that is triggered when a message translation is not found.
     */
    const EVENT_MISSING_TRANSLATION = 'missingTranslation';

    /**
     * @var bool whether to force message translation when the source and target languages are the same.
     * Defaults to false, meaning translation is only performed when source and target languages are different.
     */
    public $forceTranslation = false;
    /**
     * @var string the language that the original messages are in. If not set, it will use the value of
     * [[\yii\base\Application::sourceLanguage]].
     */
    public $sourceLanguage;

    private $_messages = [];


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
     * If translation for specific locale code such as `en-US` isn't found it
     * tries more generic `en`.
     *
     * @param string $category the message category
     * @param string $language the target language
     * @return array the loaded messages. The keys are original messages, and the values
     * are translated messages.
     */
    public function loadMessages($category, $language)
    {
        return [];
    }

    /**
     * Translates a message to the specified language.
     *
     * Note that unless [[forceTranslation]] is true, if the target language
     * is the same as the [[sourceLanguage|source language]], the message
     * will NOT be translated.
     *
     * If a translation is not found, a [[EVENT_MISSING_TRANSLATION|missingTranslation]] event will be triggered.
     *
     * @param string $category the message category
     * @param string $message the message to be translated
     * @param string $language the target language
     * @return string|bool the translated message or false if translation wasn't found or isn't required
     */
    public function translate($category, $message, $language)
    {
        if ($this->forceTranslation || $language !== $this->sourceLanguage) {
            return $this->translateMessage($category, $message, $language);
        }

        return false;
    }

    /**
     * Translates the specified message.
     * If the message is not found, a [[EVENT_MISSING_TRANSLATION|missingTranslation]] event will be triggered.
     * If there is an event handler, it may provide a [[MissingTranslationEvent::$translatedMessage|fallback translation]].
     * If no fallback translation is provided this method will return `false`.
     * @param string $category the category that the message belongs to.
     * @param string $message the message to be translated.
     * @param string $language the target language.
     * @return string|bool the translated message or false if translation wasn't found.
     */
    protected function translateMessage($category, $message, $language)
    {
        $key = $language . '/' . $category;
        if (!isset($this->_messages[$key])) {
            $this->_messages[$key] = $this->loadMessages($category, $language);
        }
        if (isset($this->_messages[$key][$message]) && $this->_messages[$key][$message] !== '') {
            return $this->_messages[$key][$message];
        } elseif ($this->hasEventHandlers(self::EVENT_MISSING_TRANSLATION)) {
            $event = new MissingTranslationEvent([
                'category' => $category,
                'message' => $message,
                'language' => $language,
            ]);
            $this->trigger(self::EVENT_MISSING_TRANSLATION, $event);
            if ($event->translatedMessage !== null) {
                return $this->_messages[$key][$message] = $event->translatedMessage;
            }
        }

        return $this->_messages[$key][$message] = false;
    }

    /**
     * Saves the message translations performing merge with the already existing ones.
     * @param string $category the category that the messages belong to.
     * @param string $language the target language.
     * @param array $messages messages to be saved in format: `[message => translation]`
     * @param array $options saving options. Available options are:
     *
     * - `markUnused`: bool, whether to mark messages that are not present in the given message set.
     * - `removeUnused`: bool, whether to remove messages that are not present in the given message set.
     * - `sort`: bool, whether to sort messages by keys when merging new messages with the existing ones.
     *   Defaults to `false`, which means the new (untranslated) messages will be separated from the old (translated) ones.
     *
     * @return int number of changed messages.
     * @since 2.1.0
     */
    public function save($category, $language, array $messages, array $options = [])
    {
        $options = array_merge([
            'markUnused' => true,
            'removeUnused' => false,
            'sort' => false,
        ], $options);

        $rawExistingMessages = $this->loadMessages($category, $language);
        $existingMessages = $rawExistingMessages;
        ksort($messages);
        ksort($existingMessages);
        if ($existingMessages === $messages && (!$options['sort'] || $rawExistingMessages == $messages)) {
            return 0;
        }

        $changeCount = 0;

        $merged = [];
        $todo = [];
        foreach ($messages as $message => $translation) {
            if (array_key_exists($message, $existingMessages)) {
                if ($existingMessages[$message] === $translation || $translation === '') {
                    $merged[$message] = $existingMessages[$message];
                } else {
                    $merged[$message] = $translation;
                    $changeCount++;
                }
            } else {
                $todo[$message] = $translation;
                $changeCount++;
            }
        }
        if ($changeCount < 1 && (!$options['sort'] || array_keys($rawExistingMessages) === array_keys($existingMessages))) {
            return $changeCount;
        }
        unset($rawExistingMessages);

        ksort($merged);
        ksort($todo);
        foreach ($existingMessages as $message => $translation) {
            if (!isset($merged[$message]) && !isset($todo[$message])) {
                if ($options['removeUnused']) {
                    $changeCount++;
                    continue;
                }

                if (!empty($translation) && (!$options['markUnused'] || (strncmp($translation, '@@', 2) === 0 && substr_compare($translation, '@@', -2, 2) === 0))) {
                    $todo[$message] = $translation;
                } else {
                    $todo[$message] = '@@' . $translation . '@@';
                    $changeCount++;
                }
            }
        }
        $merged = array_merge($todo, $merged);
        if ($options['sort']) {
            ksort($merged);
        }

        $key = $language . '/' . $category;
        unset($this->_messages[$key]);

        $this->saveMessages($category, $language, $merged, $options);
        return $changeCount;
    }

    /**
     * Saves the given message translations.
     * @param string $category the category that the messages belong to.
     * @param string $language the target language.
     * @param array $messages messages to be saved in format: `[message => translation]`
     * @param array $options saving options.
     * @since 2.1.0
     */
    abstract protected function saveMessages($category, $language, array $messages, array $options);
}
