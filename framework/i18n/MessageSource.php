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
 * MessageSource 是消息翻译类的基类。
 *
 * 消息源将翻译的消息存储在某个持久化存储中。
 *
 * 子类应覆盖 [[loadMessages()]] 以提供已翻译的消息。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class MessageSource extends Component
{
    /**
     * @event MissingTranslationEvent 当找不到一条消息翻译时触发的事件。
     */
    const EVENT_MISSING_TRANSLATION = 'missingTranslation';

    /**
     * @var bool 是否在源语言和目标语言相同时强制进行消息转换。
     * 默认为 false，表示仅在源语言和目标语言不同时执行转换。
     */
    public $forceTranslation = false;
    /**
     * @var string 原始消息的语言。
     * 如果没有设置，将使用 [[\yii\base\Application::sourceLanguage]] 的值。
     */
    public $sourceLanguage;

    private $_messages = [];


    /**
     * 初始化此组件。
     */
    public function init()
    {
        parent::init();
        if ($this->sourceLanguage === null) {
            $this->sourceLanguage = Yii::$app->sourceLanguage;
        }
    }

    /**
     * 加载指定语言和类别的消息转换。
     * 如果找不到特定区域代码的翻译，例如 `en-US`，
     * 则会尝试更通用的 `en`。
     *
     * @param string $category 消息类别
     * @param string $language 目标语言
     * @return array 加载的消息。
     * 键是原始的消息，值是翻译的消息。
     */
    protected function loadMessages($category, $language)
    {
        return [];
    }

    /**
     * 翻译消息到指定的语言。
     *
     * 请注意，除非 [[forceTranslation]] 为 true，
     * 否则如果目标语言与 [[sourceLanguage|source language]] 相同，
     * 将不会翻译该消息。
     *
     * 如果未找到翻译，将触发 [[EVENT_MISSING_TRANSLATION|missingTranslation]] 事件。
     *
     * @param string $category 消息类别
     * @param string $message 要翻译的信息
     * @param string $language 目标语言
     * @return string|bool 翻译好的消息，如果未找到或不需要翻译，则为 false
     */
    public function translate($category, $message, $language)
    {
        if ($this->forceTranslation || $language !== $this->sourceLanguage) {
            return $this->translateMessage($category, $message, $language);
        }

        return false;
    }

    /**
     * 翻译指定的消息。
     * 如果未找到该消息，将触发 [[EVENT_MISSING_TRANSLATION|missingTranslation]] 事件。
     * 如果有事件处理程序，它可能会提供 [[MissingTranslationEvent::$translatedMessage|fallback translation]].
     * 如果没有提供消息翻译，则此方法将返回 `false`。
     * @param string $category 消息所属的类别。
     * @param string $message 要翻译的信息。
     * @param string $language 目标语言。
     * @return string|bool 已翻译的消息，如果未找到翻译，则为 false。
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
}
