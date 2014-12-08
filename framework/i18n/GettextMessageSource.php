<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

use Yii;

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
     * @var string
     */
    public $basePath = '@app/messages';
    /**
     * @var string
     */
    public $catalog = 'messages';
    /**
     * @var boolean
     */
    public $useMoFile = true;
    /**
     * @var boolean
     */
    public $useBigEndian = false;


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
    protected function loadMessages($category, $language)
    {
        $messageFile = $this->getMessageFilePath($language);
        $messages = $this->loadMessagesFromFile($messageFile, $category);

        $fallbackLanguage = substr($language, 0, 2);
        if ($fallbackLanguage != $language) {
            $fallbackMessageFile = $this->getMessageFilePath($fallbackLanguage);
            $fallbackMessages = $this->loadMessagesFromFile($fallbackMessageFile, $category);

            if ($messages === null && $fallbackMessages === null && $fallbackLanguage != $this->sourceLanguage) {
                Yii::error("The message file for category '$category' does not exist: $messageFile Fallback file does not exist as well: $fallbackMessageFile", __METHOD__);
            } elseif (empty($messages)) {
                return $fallbackMessages;
            } elseif (!empty($fallbackMessages)) {
                foreach ($fallbackMessages as $key => $value) {
                    if (!empty($value) && empty($messages[$key])) {
                        $messages[$key] = $fallbackMessages[$key];
                    }
                }
            }
        } else {
            if ($messages === null) {
                Yii::error("The message file for category '$category' does not exist: $messageFile", __METHOD__);
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
        } else {
            return null;
        }
    }
}
