<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

use Yii;

/**
 * PhpMessageSource 表示在 PHP 脚本中存储已翻译消息的消息源。
 *
 * PhpMessageSource 使用 PHP 数组来保存消息翻译。
 *
 * - 每个 PHP 脚本都包含一个数组，
 *   其存储一个特定的语言和用于单个消息类别的消息翻译;
 * - 每个 PHP 脚本都保存名为 "[[basePath]]/LanguageID/CategoryName.php" 的文件；
 * - 在每个 PHP 脚本中，消息翻译将以数组形式返回，如下所示：
 *
 * ```php
 * return [
 *     'original message 1' => 'translated message 1',
 *     'original message 2' => 'translated message 2',
 * ];
 * ```
 *
 * 您可以使用 [[fileMap]] 自定义类别名称和文件名之间的关联。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class PhpMessageSource extends MessageSource
{
    /**
     * @var string 所有翻译的消息的基本路径。默认是 '@app/messages'。
     */
    public $basePath = '@app/messages';
    /**
     * @var array 消息类别与相应的消息文件路径之间的映射。
     * 文件路径位于 [[basePath]] 相对路径下。例如，
     *
     * ```php
     * [
     *     'core' => 'core.php',
     *     'ext' => 'extensions.php',
     * ]
     * ```
     */
    public $fileMap;


    /**
     * 加载指定 $language 和 $category 的消息翻译。
     * 如果找不到特定区域代码（例如 `en-US`）的翻译，则会尝试更通用的 `en`。
     * 当两者都存在时，`en-US` 消息将合并在 `en` 上。
     * 有关详细信息，请参见 [[loadFallbackMessages]]。
     * 如果 $language 不如 [[sourceLanguage]] 更具体，
     * 则该方法将尝试加载 [[sourceLanguage]] 的消息。例如：[[sourceLanguage]] 是 `en-GB`，
     * $language 是 `en`。该方法将加载 `en` 的消息并将它们合并到 `en-GB`。
     *
     * @param string $category 消息类别
     * @param string $language 目标语言
     * @return array 加载的消息。键是源消息，值是翻译的消息。
     * @see loadFallbackMessages
     * @see sourceLanguage
     */
    protected function loadMessages($category, $language)
    {
        $messageFile = $this->getMessageFilePath($category, $language);
        $messages = $this->loadMessagesFromFile($messageFile);

        $fallbackLanguage = substr($language, 0, 2);
        $fallbackSourceLanguage = substr($this->sourceLanguage, 0, 2);

        if ($language !== $fallbackLanguage) {
            $messages = $this->loadFallbackMessages($category, $fallbackLanguage, $messages, $messageFile);
        } elseif ($language === $fallbackSourceLanguage) {
            $messages = $this->loadFallbackMessages($category, $this->sourceLanguage, $messages, $messageFile);
        } else {
            if ($messages === null) {
                Yii::warning("The message file for category '$category' does not exist: $messageFile", __METHOD__);
            }
        }

        return (array) $messages;
    }

    /**
     * 该方法由 [[loadMessages]] 调用来为语言加载后备消息。
     * 方法尝试为 $fallbackLanguage 加载 $category 消息，并将它们添加到 $messages 数组中。
     *
     * @param string $category 消息类别
     * @param string $fallbackLanguage 目标后备语言
     * @param array $messages 先前加载的翻译消息的数组。
     * 键是源消息，值是翻译的消息。
     * @param string $originalMessageFile 带有消息文件的路径。
     * 用于在未找到任何翻译时记录错误信息。
     * @return array 加载的消息。键是源消息，值是翻译的消息。
     * @since 2.0.7
     */
    protected function loadFallbackMessages($category, $fallbackLanguage, $messages, $originalMessageFile)
    {
        $fallbackMessageFile = $this->getMessageFilePath($category, $fallbackLanguage);
        $fallbackMessages = $this->loadMessagesFromFile($fallbackMessageFile);

        if (
            $messages === null && $fallbackMessages === null
            && $fallbackLanguage !== $this->sourceLanguage
            && $fallbackLanguage !== substr($this->sourceLanguage, 0, 2)
        ) {
            Yii::error("The message file for category '$category' does not exist: $originalMessageFile "
                . "Fallback file does not exist as well: $fallbackMessageFile", __METHOD__);
        } elseif (empty($messages)) {
            return $fallbackMessages;
        } elseif (!empty($fallbackMessages)) {
            foreach ($fallbackMessages as $key => $value) {
                if (!empty($value) && empty($messages[$key])) {
                    $messages[$key] = $fallbackMessages[$key];
                }
            }
        }

        return (array) $messages;
    }

    /**
     * 返回指定语言和类别的消息文件路径。
     *
     * @param string $category 消息类别
     * @param string $language 目标语言
     * @return string 消息文件的路径
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
     * 加载指定语言和类别的消息翻译，如果文件不存在返回 null。
     *
     * @param string $messageFile 消息文件的路径
     * @return array|null 消息数组，如果找不到文件，则返回 null
     */
    protected function loadMessagesFromFile($messageFile)
    {
        if (is_file($messageFile)) {
            $messages = include $messageFile;
            if (!is_array($messages)) {
                $messages = [];
            }

            return $messages;
        }

        return null;
    }
}
