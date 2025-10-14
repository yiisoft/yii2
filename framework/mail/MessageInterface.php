<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\mail;

/**
 * MessageInterface is the interface that should be implemented by mail message classes.
 *
 * A message represents the settings and content of an email, such as the sender, recipient,
 * subject, body, etc.
 *
 * Messages are sent by a [[\yii\mail\MailerInterface|mailer]], like the following,
 *
 * ```
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
     * Returns the character set of this message.
     * @return string the character set of this message.
     */
    public function getCharset();

    /**
     * Sets the character set of this message.
     * @param string $charset character set name.
     * @return $this self reference.
     */
    public function setCharset($charset);

    /**
     * Returns the message sender.
     * @return string|array the sender
     */
    public function getFrom();

    /**
     * Sets the message sender.
     * @param string|array $from sender email address.
     * You may pass an array of addresses if this message is from multiple people.
     * You may also specify sender name in addition to email address using format:
     * `[email => name]`.
     * @return $this self reference.
     */
    public function setFrom($from);

    /**
     * Returns the message recipient(s).
     * @return string|array the message recipients
     */
    public function getTo();

    /**
     * Sets the message recipient(s).
     * @param string|array $to receiver email address.
     * You may pass an array of addresses if multiple recipients should receive this message.
     * You may also specify receiver name in addition to email address using format:
     * `[email => name]`.
     * @return $this self reference.
     */
    public function setTo($to);

    /**
     * Returns the reply-to address of this message.
     * @return string|array the reply-to address of this message.
     */
    public function getReplyTo();

    /**
     * Sets the reply-to address of this message.
     * @param string|array $replyTo the reply-to address.
     * You may pass an array of addresses if this message should be replied to multiple people.
     * You may also specify reply-to name in addition to email address using format:
     * `[email => name]`.
     * @return $this self reference.
     */
    public function setReplyTo($replyTo);

    /**
     * Returns the Cc (additional copy receiver) addresses of this message.
     * @return string|array the Cc (additional copy receiver) addresses of this message.
     */
    public function getCc();

    /**
     * Sets the Cc (additional copy receiver) addresses of this message.
     * @param string|array $cc copy receiver email address.
     * You may pass an array of addresses if multiple recipients should receive this message.
     * You may also specify receiver name in addition to email address using format:
     * `[email => name]`.
     * @return $this self reference.
     */
    public function setCc($cc);

    /**
     * Returns the Bcc (hidden copy receiver) addresses of this message.
     * @return string|array the Bcc (hidden copy receiver) addresses of this message.
     */
    public function getBcc();

    /**
     * Sets the Bcc (hidden copy receiver) addresses of this message.
     * @param string|array $bcc hidden copy receiver email address.
     * You may pass an array of addresses if multiple recipients should receive this message.
     * You may also specify receiver name in addition to email address using format:
     * `[email => name]`.
     * @return $this self reference.
     */
    public function setBcc($bcc);

    /**
     * Returns the message subject.
     * @return string the message subject
     */
    public function getSubject();

    /**
     * Sets the message subject.
     * @param string $subject message subject
     * @return $this self reference.
     */
    public function setSubject($subject);

    /**
     * Sets message plain text content.
     * @param string $text message plain text content.
     * @return $this self reference.
     */
    public function setTextBody($text);

    /**
     * Sets message HTML content.
     * @param string $html message HTML content.
     * @return $this self reference.
     */
    public function setHtmlBody($html);

    /**
     * Attaches existing file to the email message.
     * @param string $fileName full file name
     * @param array $options options for embed file. Valid options are:
     *
     * - fileName: name, which should be used to attach file.
     * - contentType: attached file MIME type.
     *
     * @return $this self reference.
     */
    public function attach($fileName, array $options = []);

    /**
     * Attach specified content as file for the email message.
     * @param string $content attachment file content.
     * @param array $options options for embed file. Valid options are:
     *
     * - fileName: name, which should be used to attach file.
     * - contentType: attached file MIME type.
     *
     * @return $this self reference.
     */
    public function attachContent($content, array $options = []);

    /**
     * Attach a file and return it's CID source.
     * This method should be used when embedding images or other data in a message.
     * @param string $fileName file name.
     * @param array $options options for embed file. Valid options are:
     *
     * - fileName: name, which should be used to attach file.
     * - contentType: attached file MIME type.
     *
     * @return string attachment CID.
     */
    public function embed($fileName, array $options = []);

    /**
     * Attach a content as file and return it's CID source.
     * This method should be used when embedding images or other data in a message.
     * @param string $content attachment file content.
     * @param array $options options for embed file. Valid options are:
     *
     * - fileName: name, which should be used to attach file.
     * - contentType: attached file MIME type.
     *
     * @return string attachment CID.
     */
    public function embedContent($content, array $options = []);

    /**
     * Sends this email message.
     * @param MailerInterface|null $mailer the mailer that should be used to send this message.
     * If null, the "mailer" application component will be used instead.
     * @return bool whether this message is sent successfully.
     */
    public function send(?MailerInterface $mailer = null);

    /**
     * Returns string representation of this message.
     * @return string the string representation of this message.
     */
    public function toString();
}
