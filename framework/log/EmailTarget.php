<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\log;

use Yii;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\mail\MailerInterface;

/**
 * EmailTarget 将所选日志消息发送到指定的电子邮件地址。
 *
 * 您可以通过设置 [[message]] 属性来配置要发送的电子邮件。
 * 您可以通过该属性设置目标电子邮件地址，主题等：
 *
 * ```php
 * 'components' => [
 *     'log' => [
 *          'targets' => [
 *              [
 *                  'class' => 'yii\log\EmailTarget',
 *                  'mailer' => 'mailer',
 *                  'levels' => ['error', 'warning'],
 *                  'message' => [
 *                      'from' => ['log@example.com'],
 *                      'to' => ['developer1@example.com', 'developer2@example.com'],
 *                      'subject' => 'Log message',
 *                  ],
 *              ],
 *          ],
 *     ],
 * ],
 * ```
 *
 * 在上面的 `mailer` 中，是发送电子邮件的组件的 ID，应该已经配置好了。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class EmailTarget extends Target
{
    /**
     * @var array 用于创建 [[\yii\mail\MessageInterface|message]] 对象的配置数组。
     * 请注意，必须设置“to”选项，该选项指定目标电子邮件地址。
     */
    public $message = [];
    /**
     * @var MailerInterface|array|string 邮件程序对象或邮件程序对象的应用程序组件 ID。
     * 创建 EmailTarget 对象后，只有它是邮件程序对象时，才可以更改它的属性。
     *
     * 从 2.0.2 版开始，这也可以是用于创建对象的配置数组。
     */
    public $mailer = 'mailer';


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if (empty($this->message['to'])) {
            throw new InvalidConfigException('The "to" option must be set for EmailTarget::message.');
        }
        $this->mailer = Instance::ensure($this->mailer, 'yii\mail\MailerInterface');
    }

    /**
     * 将日志消息发送到指定的电子邮件地址。
     * 从版本 2.0.14 开始，如果无法导出日志，此方法将抛出 LogRuntimeException 异常。
     * @throws LogRuntimeException
     */
    public function export()
    {
        // moved initialization of subject here because of the following issue
        // https://github.com/yiisoft/yii2/issues/1446
        if (empty($this->message['subject'])) {
            $this->message['subject'] = 'Application Log';
        }
        $messages = array_map([$this, 'formatMessage'], $this->messages);
        $body = wordwrap(implode("\n", $messages), 70);
        $message = $this->composeMessage($body);
        if (!$message->send($this->mailer)) {
            throw new LogRuntimeException('Unable to export log through email!');
        }
    }

    /**
     * 使用给定的正文内容编写邮件消息。
     * @param string $body 邮件正文
     * @return \yii\mail\MessageInterface $message
     */
    protected function composeMessage($body)
    {
        $message = $this->mailer->compose();
        Yii::configure($message, $this->message);
        $message->setTextBody($body);

        return $message;
    }
}
