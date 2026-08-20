<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\log;

use yii\mail\BaseMessage;

/**
 * Email message stub that implements abstract operations as no-ops.
 */
class EmailMessageStub extends BaseMessage
{
    public function getCharset()
    {
    }

    public function setCharset($charset)
    {
        return $this;
    }

    public function getFrom()
    {
    }

    public function setFrom($from)
    {
        return $this;
    }

    public function getTo()
    {
    }

    public function setTo($to)
    {
        return $this;
    }

    public function getCc()
    {
    }

    public function setCc($cc)
    {
        return $this;
    }

    public function getBcc()
    {
    }

    public function setBcc($bcc)
    {
        return $this;
    }

    public function getSubject()
    {
    }

    public function setSubject($subject)
    {
        return $this;
    }

    public function getReplyTo()
    {
    }

    public function setReplyTo($replyTo)
    {
        return $this;
    }

    public function setTextBody($text)
    {
        return $this;
    }

    public function setHtmlBody($html)
    {
        return $this;
    }

    public function attachContent($content, array $options = [])
    {
    }

    public function attach($fileName, array $options = [])
    {
    }

    public function embed($fileName, array $options = [])
    {
    }

    public function embedContent($content, array $options = [])
    {
    }

    public function toString()
    {
        return '';
    }
}
