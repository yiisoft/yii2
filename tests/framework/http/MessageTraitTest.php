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

        $message->setProtocolVersion('2.0');
        $this->assertSame('2.0', $message->getProtocolVersion());

        $newMessage = $message->withProtocolVersion('2.1');
        $this->assertNotSame($newMessage, $message);
        $this->assertSame('2.1', $newMessage->getProtocolVersion());
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

        $message->setBody([
            '__class' => FileStream::class
        ]);
        $this->assertTrue($message->getBody() instanceof FileStream);

        $body = new MemoryStream();
        $newMessage = $message->withBody($body);
        $this->assertNotSame($newMessage, $message);
        $this->assertSame($body, $newMessage->getBody());
    }

    /**
     * @depends testSetupBody
     */
    public function testDefaultBody()
    {
        $message = new TestMessage();
        $this->assertTrue($message->getBody() instanceof MemoryStream);
    }

    public function testSetupHeaders()
    {
        $message = new TestMessage();

        $this->assertFalse($message->hasHeader('some'));
        $headerMessage = $message->withHeader('some', 'foo');
        $this->assertNotSame($headerMessage, $message);
        $this->assertTrue($headerMessage->hasHeader('some'));
        $this->assertEquals(['some' => ['foo']], $headerMessage->getHeaders());

        $headerAddedMessage = $headerMessage->withAddedHeader('some', 'another');
        $this->assertNotSame($headerMessage, $headerAddedMessage);
        $this->assertEquals(['some' => ['foo', 'another']], $headerAddedMessage->getHeaders());
        $this->assertEquals(['foo', 'another'], $headerAddedMessage->getHeader('some'));
        $this->assertEquals('foo,another', $headerAddedMessage->getHeaderLine('some'));

        $overrideMessage = $headerAddedMessage->withHeader('some', 'override');
        $this->assertNotSame($headerAddedMessage, $overrideMessage);
        $this->assertEquals(['some' => ['override']], $overrideMessage->getHeaders());

        $clearMessage = $headerMessage->withoutHeader('some');
        $this->assertNotSame($headerMessage, $clearMessage);
        $this->assertFalse($clearMessage->hasHeader('some'));
        $this->assertEquals([], $clearMessage->getHeader('some'));
        $this->assertEquals('', $clearMessage->getHeaderLine('some'));

        $message->setHeaders([
            'some' => ['line1', 'line2']
        ]);
        $this->assertEquals(['some' => ['line1', 'line2']], $message->getHeaders());
        $message->setHeaders([
            'another' => ['one']
        ]);
        $this->assertEquals(['another' => ['one']], $message->getHeaders());
    }

    /**
     * @depends testSetupProtocolVersion
     * @depends testSetupBody
     * @depends testSetupHeaders
     */
    public function testCreateFromConfig()
    {
        $message = new TestMessage([
            'protocolVersion' => '2.1',
            'headers' => [
                'header' => [
                    'line1',
                    'line2',
                ],
            ],
            'body' => [
                '__class' => FileStream::class
            ],
        ]);

        $this->assertSame('2.1', $message->getProtocolVersion());
        $this->assertEquals(['header' => ['line1', 'line2']], $message->getHeaders());
        $this->assertTrue($message->getBody() instanceof FileStream);
    }
}

class TestMessage extends BaseObject implements MessageInterface
{
    use MessageTrait;
}