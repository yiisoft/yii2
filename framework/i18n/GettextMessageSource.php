<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

use Yii;

/**
 * GettextMessageSource 表示基于 GNU Gettext 的消息源。
 *
 * 每个 GettextMessageSource 实例表示单个域的消息翻译。
 * 每个消息类别表示 Gettext 中的消息上下文。
 * 翻译后的消息存储为 MO 或 PO 文件，
 * 具体取决于 [[useMoFile]] 属性值。
 *
 * 所有翻译都保存在 [[basePath]] 目录下。
 *
 * 一种语言的翻译将以 MO 或 PO 文件保存在一个单独的子目录下，
 * 该子目录的名称是语言 ID。
 * 文件名通过 [[catalog]] 属性指定，默认为 'messages'。
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
     * @var bool
     */
    public $useMoFile = true;
    /**
     * @var bool
     */
    public $useBigEndian = false;


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
        $messageFile = $this->getMessageFilePath($language);
        $messages = $this->loadMessagesFromFile($messageFile, $category);

        $fallbackLanguage = substr($language, 0, 2);
        $fallbackSourceLanguage = substr($this->sourceLanguage, 0, 2);

        if ($fallbackLanguage !== $language) {
            $messages = $this->loadFallbackMessages($category, $fallbackLanguage, $messages, $messageFile);
        } elseif ($language === $fallbackSourceLanguage) {
            $messages = $this->loadFallbackMessages($category, $this->sourceLanguage, $messages, $messageFile);
        } else {
            if ($messages === null) {
                Yii::error("The message file for category '$category' does not exist: $messageFile", __METHOD__);
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
        $fallbackMessageFile = $this->getMessageFilePath($fallbackLanguage);
        $fallbackMessages = $this->loadMessagesFromFile($fallbackMessageFile, $category);

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
     * @param string $language 目标语言
     * @return string 消息文件的路径
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
     * 加载指定语言和类别的信息翻译，如果文件不存在返回 null。
     *
     * @param string $messageFile 消息文件的路径
     * @param string $category 消息类别
     * @return array|null 消息数组，如果找不到文件，则返回 null
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
