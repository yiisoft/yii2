<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\http;

use Psr\Http\Message\MessageInterface;
use yii\base\BaseObject;
use yii\http\FileStream;
use yii\http\MemoryStream;
use yii\http\MessageTrait;
use yiiunit\TestCase;

class MessageTraitTest extends TestCase
{
    public function testSetupProtocolVersion()
    {
        $message = new TestMessage();

        $this->assertSame($message, $message->withProtocolVersion('2.0'));
        $this->assertSame('2.0', $message->getProtocolVersion());

        $message->setProtocolVersion('2.1');
        $this->assertSame('2.1', $message->getProtocolVersion());
    }

    /**
     * @depends testSetupProtocolVersion
     */
    public function testDefaultProtocolVersion()
    {
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.2';
        $message = new TestMessage();
        $this->assertSame('1.2', $message->getProtocolVersion());

        unset($_SERVER['SERVER_PROTOCOL']);
        $message = new TestMessage();
        $this->assertSame('1.0', $message->getProtocolVersion());
    }

    public function testSetupBody()
    {
        $message = new TestMessage();

        $body = new MemoryStream();
        $this->assertSame($message, $message->withBody($body));
        $this->assertSame($body, $message->getBody());

        $message->setBody([
            'class' => FileStream::class
        ]);
        $this->assertTrue($message->getBody() instanceof FileStream);
    }

    /**
     * @depends testSetupBody
     */
    public function testDefaultBody()
    {
        $message = new TestMessage();
        $this->assertTrue($message->getBody() instanceof MemoryStream);
    }

    /**
     * @depends testSetupProtocolVersion
     * @depends testSetupBody
     */
    public function testCreateFromConfig()
    {
        $message = new TestMessage([
            'protocolVersion' => '2.1',
            'body' => [
                'class' => FileStream::class
            ],
        ]);

        $this->assertSame('2.1', $message->getProtocolVersion());
        $this->assertTrue($message->getBody() instanceof FileStream);
    }
}

class TestMessage extends BaseObject implements MessageInterface
{
    use MessageTrait;
}