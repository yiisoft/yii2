<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

use yii\base\Event;

/**
 * MissingTranslationEvent 表示 [[MessageSource::EVENT_MISSING_TRANSLATION]] 事件的参数。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class MissingTranslationEvent extends Event
{
    /**
     * @var string 要翻译的信息。事件处理程序可以使用它来提供一个回退转换，
     * 并在可能的情况下设置 [[translatedMessage]]。
     */
    public $message;
    /**
     * @var string 翻译好的消息。事件处理程序可以使用 [[message]] 的翻译版本覆盖此属性。
     * 如果未设置（null），则表示消息未被翻译。
     */
    public $translatedMessage;
    /**
     * @var string 消息所属的类别
     */
    public $category;
    /**
     * @var string 要将消息翻译成的语言 ID（例如 en-US）
     */
    public $language;
}
