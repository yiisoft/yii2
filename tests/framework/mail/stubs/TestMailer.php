<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\mail\stubs;

use yii\mail\BaseMailer;

/**
 * Test Mailer class.
 */
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
