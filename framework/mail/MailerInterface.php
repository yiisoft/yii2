<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mail;

/**
 * MailerInterface 是应该由邮件程序类实现的接口。
 *
 * 邮件程序应主要支持创建和发送 [[MessageInterface|mail messages]]。
 * 它还应该通过视图渲染机制支持消息体的组合。例如，
 *
 * ```php
 * Yii::$app->mailer->compose('contact/html', ['contactForm' => $form])
 *     ->setFrom('from@domain.com')
 *     ->setTo($form->email)
 *     ->setSubject($form->subject)
 *     ->send();
 * ```
 *
 * @see MessageInterface
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
interface MailerInterface
{
    /**
     * 创建新的消息实例，并可选择通过视图渲染来组合其正文内容。
     *
     * @param string|array|null $view 用于渲染邮件内容的视图。可以是：
     *
     * - 字符串，表示用于渲染邮件的 HTML 正文视图名称或 [路径别名](guide:concept-aliases)。
     *   在这种情况下，文本主体将通过在 HTML 主体中应用 `strip_tags()` 来生成。
     * - 一个带有 'html' 和/或 'text' 元素的数组。'html' 元素指的是用于渲染 HTML 主体的视图名称或路径别名，
     *   而 'text' 元素是用于渲染正文的。例如，
     *   `['html' => 'contact-html', 'text' => 'contact-text']`。
     * - 空，表示是消息实例将在没有正文内容的情况下返回。
     *
     * @param array $params 将提取并且在视图文件中用的参数（键值对）。
     * @return MessageInterface 消息实例。
     */
    public function compose($view = null, array $params = []);

    /**
     * 发送给定的邮件。
     * @param MessageInterface $message 要发送的电子邮件实例
     * @return bool 消息是否已成功发送
     */
    public function send($message);

    /**
     * 一次发送多条消息
     *
     * 此方法可以支持更高效的在同一批中发送多个消息。
     *
     * @param array $messages 应发送的电子邮件列表。
     * @return int 成功发送的消息数。
     */
    public function sendMultiple(array $messages);
}
