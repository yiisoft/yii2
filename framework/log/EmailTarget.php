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
 * EmailTarget sends selected log messages to the specified email addresses.
 *
 * You may configure the email to be sent by setting the [[message]] property, through which
 * you can set the target email addresses, subject, etc.:
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
 * In the above `mailer` is ID of the component that sends email and should be already configured.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class EmailTarget extends Target
{
    /**
     * @var array the configuration array for creating a [[\yii\mail\MessageInterface|message]] object.
     * Note that the "to" option must be set, which specifies the destination email address(es).
     */
    public $message = [];
    /**
     * @var MailerInterface|array|string the mailer object or the application component ID of the mailer object.
     * After the EmailTarget object is created, if you want to change this property, you should only assign it
     * with a mailer object.
     * Starting from version 2.0.2, this can also be a configuration array for creating the object.
     */
    public $mailer = 'mailer';


    /**
     * @inheritdoc
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
     * Sends log messages to specified email addresses.
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
        $this->composeMessage($body)->send($this->mailer);
    }

    /**
     * Composes a mail message with the given body content.
     * @param string $body the body content
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
