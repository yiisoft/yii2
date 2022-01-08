<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

use Yii;
use yii\base\InvalidArgumentException;

/**
 * GettextMessageSource represents a message source that is based on GNU Gettext.
 *
 * Each GettextMessageSource instance represents the message translations
 * for a single domain. And each message category represents a message context
 * in Gettext. Translated messages are stored as either a MO or PO file,
 * depending on the [[useMoFile]] property value.
 *
 * All translations are saved under the [[basePath]] directory.
 *
 * Translations in one language are kept as MO or PO files under an individual
 * subdirectory whose name is the language ID. The file name is specified via
 * [[catalog]] property, which defaults to 'messages'.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class GettextMessageSource extends MessageSource
{
    const MO_FILE_EXT = '.mo';
    const PO_FILE_EXT = '.po';

    /**
     * @var string base directory of messages files
     */
    public $basePath = '@app/messages';
    /**
     * @var string sub-directory of messages files
     */
    public $catalog = 'messages';
    /**
     * @var bool whether to use generated MO files
     */
    public $useMoFile = true;
    /**
     * @var bool whether to use big-endian when reading and writing an integer
     */
    public $useBigEndian = false;


    /**
     * Loads the message translation for the specified $language and $category.
     * If translation for specific locale code such as `en-US` isn't found it
     * tries more generic `en`. When both are present, the `en-US` messages will be merged
     * over `en`. See [[loadFallbackMessages]] for details.
     * If the $language is less specific than [[sourceLanguage]], the method will try to
     * load the messages for [[sourceLanguage]]. For example: [[sourceLanguage]] is `en-GB`,
     * $language is `en`. The method will load the messages for `en` and merge them over `en-GB`.
     *
     * @param string $category the message category
     * @param string $language the target language
     * @return array the loaded messages. The keys are original messages, and the values are translated messages.
     * @see loadFallbackMessages
     * @see sourceLanguage
     */
    protected function loadMessages($category, $language)
    {
        $messageFile = $this->getMessageFilePath($language);
        $messages = $this->loadMessagesFromFile($messageFile, $category);

        $fallbackLanguage = substr($language, 0, 2);
        $fallbackSourceLanguage = substr($this->sourceLanguage, 0, 2);

        if ($fallbackLanguage !== '' && $fallbackLanguage !== $language) {
            $messages = $this->loadFallbackMessages($category, $fallbackLanguage, $messages, $messageFile);
        } elseif ($fallbackSourceLanguage !== '' && $language === $fallbackSourceLanguage) {
            $messages = $this->loadFallbackMessages($category, $this->sourceLanguage, $messages, $messageFile);
        } elseif ($messages === null) {
            Yii::error("The message file for category '$category' does not exist: $messageFile", __METHOD__);
        }

        return (array) $messages;
    }

    /**
     * The method is normally called by [[loadMessages]] to load the fallback messages for the language.
     * Method tries to load the $category messages for the $fallbackLanguage and adds them to the $messages array.
     *
     * @param string $category the message category
     * @param string $fallbackLanguage the target fallback language
     * @param array $messages the array of previously loaded translation messages.
     * The keys are original messages, and the values are the translated messages.
     * @param string $originalMessageFile the path to the file with messages. Used to log an error message
     * in case when no translations were found.
     * @return array the loaded messages. The keys are original messages, and the values are the translated messages.
     * @since 2.0.7
     */
    protected function loadFallbackMessages($category, $fallbackLanguage, $messages, $originalMessageFile)
    {
        $fallbackMessageFile = $this->getMessageFilePath($fallbackLanguage);
        $fallbackMessages = $this->loadMessagesFromFile($fallbackMessageFile, $category);

        if (
            $messages === null && $fallbackMessages === null
            && $fallbackLanguage !== $this->sourceLanguage
            && strpos($this->sourceLanguage, $fallbackLanguage) !== 0
        ) {
            Yii::error("The message file for category '$category' does not exist: $originalMessageFile "
                . "Fallback file does not exist as well: $fallbackMessageFile", __METHOD__);
        } elseif (empty($messages)) {
            return $fallbackMessages;
        } elseif (!empty($fallbackMessages)) {
            foreach ($fallbackMessages as $key => $value) {
                if (!empty($value) && empty($messages[$key])) {
                    $messages[$key] = $value;
                }
            }
        }

        return (array) $messages;
    }

    /**
     * Returns message file path for the specified language and category.
     *
     * @param string $language the target language
     * @return string path to message file
     */
    protected function getMessageFilePath($language)
    {
        $language = (string) $language;
        if ($language !== '' && !preg_match('/^[a-z0-9_-]+$/i', $language)) {
            throw new InvalidArgumentException(sprintf('Invalid language code: "%s".', $language));
        }
        $messageFile = Yii::getAlias($this->basePath) . '/' . $language . '/' . $this->catalog;
        if ($this->useMoFile) {
            $messageFile .= self::MO_FILE_EXT;
        } else {
            $messageFile .= self::PO_FILE_EXT;
        }

        return $messageFile;
    }

    /**
     * Loads the message translation for the specified language and category or returns null if file doesn't exist.
     *
     * @param string $messageFile path to message file
     * @param string $category the message category
     * @return array|null array of messages or null if file not found
     */
    protected function loadMessagesFromFile($messageFile, $category)
    {
        if (is_file($messageFile)) {
            if ($this->useMoFile) {
                $gettextFile = new GettextMoFile(['useBigEndian' => $this->useBigEndian]);
            } else {
                $gettextFile = new GettextPoFile();
            }
            $messages = $gettextFile->load($messageFile, $category);
            if (!is_array($messages)) {
                $messages = [];
            }

            return $messages;
        }

        return null;
    }
}
