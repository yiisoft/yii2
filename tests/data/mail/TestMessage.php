<?php

namespace yiiunit\data\mail;

use yii\mail\BaseMessage;

/**
 * Test Message class
 */
class TestMessage extends BaseMessage
{
    public $id;
    public $encoding;
    public $_charset;
    public $_from;
    public $_replyTo;
    public $_to;
    public $_cc;
    public $_bcc;
    public $_subject;
    public $_textBody;
    public $_htmlBody;

    public function getCharset()
    {
        return $this->_charset;
    }

    public function setCharset($charset)
    {
        $this->_charset = $charset;

        return $this;
    }

    public function getFrom()
    {
        return $this->_from;
    }

    public function setFrom($from)
    {
        $this->_from = $from;

        return $this;
    }

    public function getTo()
    {
        return $this->_to;
    }

    public function setTo($to)
    {
        $this->_to = $to;

        return $this;
    }

    public function getCc()
    {
        return $this->_cc;
    }

    public function setCc($cc)
    {
        $this->_cc = $cc;

        return $this;
    }

    public function getBcc()
    {
        return $this->_bcc;
    }

    public function setBcc($bcc)
    {
        $this->_bcc = $bcc;

        return $this;
    }

    public function getSubject()
    {
        return $this->_subject;
    }

    public function setSubject($subject)
    {
        $this->_subject = $subject;

        return $this;
    }

    public function getReplyTo()
    {
        return $this->_replyTo;
    }

    public function setReplyTo($replyTo)
    {
        $this->_replyTo = $replyTo;

        return $this;
    }

    public function setTextBody($text)
    {
        $this->_textBody = $text;

        return $this;
    }

    public function setHtmlBody($html)
    {
        $this->_htmlBody = $html;

        return $this;
    }

    public function attachContent($content, array $options = []) {}

    public function attach($fileName, array $options = []) {}

    public function embed($fileName, array $options = []) {}

    public function embedContent($content, array $options = []) {}

    public function toString()
    {
        $mailer = $this->mailer;
        $this->mailer = null;
        $s = var_export($this, true);
        $this->mailer = $mailer;
        return $s;
    }

    public function addHeader($name, $value) {}

    public function setHeader($name, $value) {}

    public function getHeader($name) {}

    public function setHeaders($headers) {}
}