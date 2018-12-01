<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mail;

use yii\base\Event;

/**
 * MailEvent 表示用于由 [[BaseMailer]] 触发的事件参数。
 *
 * 通过设置 [[isValid]] 属性，可以控制是否继续运行该操作。
 *
 * @author Mark Jebri <mark.github@yandex.ru>
 * @since 2.0
 */
class MailEvent extends Event
{
    /**
     * @var \yii\mail\MessageInterface 正在发送的邮件消息。
     */
    public $message;
    /**
     * @var bool 消息是否成功发送。
     */
    public $isSuccessful;
    /**
     * @var bool 是否继续发送电子邮件。
     * [[\yii\mail\BaseMailer::EVENT_BEFORE_SEND]] 的事件处理程序可以设置此属性
     * 来决定是否继续发送。
     */
    public $isValid = true;
}
