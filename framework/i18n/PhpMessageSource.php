<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

use Yii;

/**
 * PhpMessageSource represents a message source that stores translated messages in PHP scripts.
 *
 * PhpMessageSource uses PHP arrays to keep message translations.
 *
 * - Each PHP script contains one array which stores the message translations in one particular
 *   language and for a single message category;
 * - Each PHP script is saved as a file named as `[[basePath]]/LanguageID/CategoryName.php`;
 * - Within each PHP script, the message translations are returned as an array like the following:
 *
 * ~~~
 * return [
 *     'original message 1' => 'translated message 1',
 *     'original message 2' => 'translated message 2',
 * ];
 * ~~~
 *
 * You may use [[fileMap]] to customize the association between category names and the file names.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class PhpMessageSource extends MessageSource
{
    /**
     * @var string the base path for all translated messages. Defaults to null, meaning
     * the "messages" subdirectory of the application directory (e.g. "protected/messages").
     */
    public $basePath = '@app/messages';
    /**
     * @var array mapping between message categories and the corresponding message file paths.
     * The file paths are relative to [[basePath]]. For example,
     *
     * ~~~
     * [
     *     'core' => 'core.php',
     *     'ext' => 'extensions.php',
     * ]
     * ~~~
     */
    public $fileMap;

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
        $messageFile = $this->getMessageFilePath($category, $language);
        $messages = $this->loadMessagesFromFile($messageFile);

        $fallbackLanguage = substr($language, 0, 2);
        if ($fallbackLanguage != $language) {
            $fallbackMessageFile = $this->getMessageFilePath($category, $fallbackLanguage);
            $fallbackMessages = $this->loadMessagesFromFile($fallbackMessageFile);

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
     * @param string $category the message category
     * @param string $language the target language
     * @return string path to message file
     */
    protected function getMessageFilePath($category, $language)
    {
        $messageFile = Yii::getAlias($this->basePath) . "/$language/";
        if (isset($this->fileMap[$category])) {
            $messageFile .= $this->fileMap[$category];
        } else {
            $messageFile .= str_replace('\\', '/', $category) . '.php';
        }

        return $messageFile;
    }

    /**
     * Loads the message translation for the specified language and category or returns null if file doesn't exist.
     *
     * @param $messageFile string path to message file
     * @return array|null array of messages or null if file not found
     */
    protected function loadMessagesFromFile($messageFile)
    {
        if (is_file($messageFile)) {
            $messages = include($messageFile);
            if (!is_array($messages)) {
                $messages = [];
            }

            return $messages;
        } else {
            return null;
        }
    }
}
