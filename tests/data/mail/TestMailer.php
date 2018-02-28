<?php

namespace yiiunit\data\mail;

use yii\mail\BaseMailer;

class TestMailer extends BaseMailer
{
    public $messageClass = TestMessage::class;
    public $sentMessages = [];

    protected function sendMessage($message)
    {
        $this->sentMessages[] = $message;
        return true;
    }
}