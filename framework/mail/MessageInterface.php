<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mail;

/**
 * MessageInterface 是应由邮件消息类实现的接口。
 *
 * 消息表示电子邮件的设置和内容，例如发件人，收件人，主题，
 * 正文，等等。
 *
 * 邮件由 [[\yii\mail\MailerInterface|mailer]] 来发送，如下，
 *
 * ```php
 * Yii::$app->mailer->compose()
 *     ->setFrom('from@domain.com')
 *     ->setTo($form->email)
 *     ->setSubject($form->subject)
 *     ->setTextBody('Plain text content')
 *     ->setHtmlBody('<b>HTML content</b>')
 *     ->send();
 * ```
 *
 * @see MailerInterface
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
interface MessageInterface
{
    /**
     * 返回邮件的字符集。
     * @return string 此邮件的字符集。
     */
    public function getCharset();

    /**
     * 设置此邮件的字符集。
     * @param string $charset 字符集名称。
     * @return $this self reference.
     */
    public function setCharset($charset);

    /**
     * 返回邮件发件人。
     * @return string|array 发件人
     */
    public function getFrom();

    /**
     * 设置邮件发送人。
     * @param string|array $from 发件人邮箱地址。
     * 如果邮件来自多个人，你可以传递地址的数组。
     * 你还可以使用以下格式指定电子邮件地址以外的发件人姓名：
     * `[email => name]`.
     * @return $this self reference.
     */
    public function setFrom($from);

    /**
     * 返回邮件收件人。
     * @return string|array 邮件收件人
     */
    public function getTo();

    /**
     * 设置邮件接收人。
     * @param string|array $to 收件人邮箱地址。
     * 如果多个收件人应该收到此邮件，你可以传递地址的数组。
     * 你还可以使用以下格式指定除电子邮件地址之外的收件人姓名：
     * `[email => name]`.
     * @return $this self reference.
     */
    public function setTo($to);

    /**
     * 返回此邮件的回复地址。
     * @return string|array 此邮件的回复地址。
     */
    public function getReplyTo();

    /**
     * 设置邮件的回复地址。
     * @param string|array $replyTo 回复地址。
     * 如果此邮件应该回复给多个人，你可以传递地址的数组。
     * 你还可以使用以下格式指定电子邮件地址以外的回复姓名：
     * `[email => name]`.
     * @return $this self reference.
     */
    public function setReplyTo($replyTo);

    /**
     * 返回邮件的 Cc（additional copy receiver）的地址。
     * @return string|array 邮件的 Cc（additional copy receiver）地址。
     */
    public function getCc();

    /**
     * 设置邮件的 Cc（additional copy receiver。
     * @param string|array $cc 复制接收者电子邮件地址。
     * 如果多个收件人应该接收到此邮件，则可以传递地址数组。
     * 还可以使用格式指定电子邮件地址的接收者名称：
     * `[email => name]`.
     * @return $this self reference.
     */
    public function setCc($cc);

    /**
     * 返回邮件的 Bcc（hidden copy receiver）的地址。
     * @return string|array 邮件的 Bcc（hidden copy receiver）地址。
     */
    public function getBcc();

    /**
     * 设置邮件的 Bcc（hidden copy receiver）地址。
     * @param string|array $bcc 隐藏的副本收件人邮箱地址。
     * 如果多个收件人应该收到此邮件，你可以传递地址的数组。
     * 你还可以使用以下格式指定除电子邮件地址之外的收件人姓名：
     * `[email => name]`.
     * @return $this self reference.
     */
    public function setBcc($bcc);

    /**
     * 返回邮件主题。
     * @return string 邮件主题
     */
    public function getSubject();

    /**
     * 设置邮件主题。
     * @param string $subject 邮件主题
     * @return $this self reference.
     */
    public function setSubject($subject);

    /**
     * 设置邮件纯文本内容。
     * @param string $text 邮件纯文本内容。
     * @return $this self reference.
     */
    public function setTextBody($text);

    /**
     * 设置邮件的 HTML 内容。
     * @param string $html 邮件 HTML 内容。
     * @return $this self reference.
     */
    public function setHtmlBody($html);

    /**
     * 将现有文件附加到邮件中。
     * @param string $fileName 文件名全称
     * @param array $options 嵌入文件的选项。有效选项包括：
     *
     * - fileName：应该用于附加文件的名称。
     * - contentType：附加文件的 MIME 类型。
     *
     * @return $this self reference.
     */
    public function attach($fileName, array $options = []);

    /**
     * 将指定的内容附加为邮件的文件。
     * @param string $content 附加文件内容。
     * @param array $options 嵌入文件的选项。有效选项包括：
     *
     * - fileName：用于附件文件的名称。
     * - contentType：附加文件的 MIME 类型。
     *
     * @return $this self reference.
     */
    public function attachContent($content, array $options = []);

    /**
     * 附加文件并返回它的 CID 源。
     * 在邮件中嵌入图像或其他数据时，应使用此方法。
     * @param string $fileName 文件名。
     * @param array $options 嵌入文件的选项。有效选项包括：
     *
     * - fileName：用于附件文件的名称。
     * - contentType：附加文件的 MIME 类型。
     *
     * @return string 附件的 CID。
     */
    public function embed($fileName, array $options = []);

    /**
     * 将内容作为文件附加并返回其 CID 源。
     * 在邮件中嵌入图像或其他数据时，应使用此方法。
     * @param string $content 附件文件内容。
     * @param array $options 嵌入文件的选项。有效选项包括：
     *
     * - fileName：用于附件文件的名称。
     * - contentType：附加文件的 MIME 类型。
     *
     * @return string attachment CID.
     */
    public function embedContent($content, array $options = []);

    /**
     * 发送此电子邮件。
     * @param MailerInterface $mailer 应该用于发送此消息的邮件程序。
     * 如果为 null，将使用 "mail" 应用程序组件。
     * @return bool 此邮件是否已成功发送。
     */
    public function send(MailerInterface $mailer = null);

    /**
     * Returns string 邮件的表现。
     * @return string 此邮件的字符串表现形式。
     */
    public function toString();
}
