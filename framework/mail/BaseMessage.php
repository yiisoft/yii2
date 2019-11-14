<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mail;

use Yii;
use yii\base\BaseObject;
use yii\base\ErrorHandler;

/**
 * BaseMessage 用于实现 [[MessageInterface]] 所需的 [[send()]] 方法的基类。
 *
 * 默认情况下，[[send()]] 将使用 "mail" 应用程序组件发送消息。
 * "mail" 应用程序组件应该是实现 [[MailerInterface]] 的邮件程序实例。
 *
 * @see BaseMailer
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
abstract class BaseMessage extends BaseObject implements MessageInterface
{
    /**
     * @var MailerInterface 创建此消息的邮件程序实例。
     * 对于独立创建的消息，应该为 `null`。
     */
    public $mailer;


    /**
     * 发送电子邮件。
     * @param MailerInterface $mailer 应该用于发送消息的邮件程序。
     * 如果没有给出邮件程序，首先应该检查是否设置了 [[mailer]]，
     * 如果没有，应该使用 "mail" 应用程序组件。
     * @return bool whether this message is sent successfully.
     */
    public function send(MailerInterface $mailer = null)
    {
        if ($mailer === null && $this->mailer === null) {
            $mailer = Yii::$app->getMailer();
        } elseif ($mailer === null) {
            $mailer = $this->mailer;
        }

        return $mailer->send($this);
    }

    /**
     * PHP 魔法方法，返回这个对象的字符串表现形式。
     * @return string 对象的字符串表现形式。
     */
    public function __toString()
    {
        // __toString cannot throw exception
        // use trigger_error to bypass this limitation
        try {
            return $this->toString();
        } catch (\Exception $e) {
            ErrorHandler::convertExceptionToError($e);
            return '';
        }
    }
}
