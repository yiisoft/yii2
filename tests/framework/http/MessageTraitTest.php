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

    public function testSetupHeaders()
    {
        $message = new TestMessage();

        $this->assertFalse($message->hasHeader('some'));
        $this->assertSame($message, $message->withHeader('some', 'foo'));
        $this->assertTrue($message->hasHeader('some'));
        $this->assertEquals(['some' => ['foo']], $message->getHeaders());

        $this->assertSame($message, $message->withAddedHeader('some', 'another'));
        $this->assertEquals(['some' => ['foo', 'another']], $message->getHeaders());
        $this->assertEquals(['foo', 'another'], $message->getHeader('some'));
        $this->assertEquals('foo,another', $message->getHeaderLine('some'));
        $this->assertSame($message, $message->withHeader('some', 'override'));
        $this->assertEquals(['some' => ['override']], $message->getHeaders());

        $this->assertSame($message, $message->withoutHeader('some'));
        $this->assertFalse($message->hasHeader('some'));
        $this->assertEquals([], $message->getHeader('some'));
        $this->assertEquals('', $message->getHeaderLine('some'));

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
                'class' => FileStream::class
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