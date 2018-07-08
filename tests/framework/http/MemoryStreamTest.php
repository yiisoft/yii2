<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\http;

use yii\http\MemoryStream;
use yiiunit\TestCase;

class MemoryStreamTest extends TestCase
{
    public function testWrite()
    {
        $stream = new MemoryStream();

        $this->assertTrue($stream->isWritable());

        $this->assertSame(5, $stream->write('01234'));
        $this->assertSame(5, $stream->write('56789'));

        $this->assertSame('0123456789', (string)$stream);
    }

    /**
     * @depends testWrite
     */
    public function testRead()
    {
        $stream = new MemoryStream();
        $stream->write('0123456789');

        $this->assertTrue($stream->isReadable());

        $stream->rewind();

        $this->assertSame('01234', $stream->read(5));
        $this->assertFalse($stream->eof());
        $this->assertSame('56789', $stream->read(6));
        $this->assertTrue($stream->eof());
    }

    /**
     * @depends testRead
     */
    public function testSeek()
    {
        $stream = new MemoryStream();
        $stream->write('0123456789');
        $stream->rewind();

        $this->assertTrue($stream->isSeekable());

        $stream->seek(5);
        $this->assertSame('56789', $stream->read(5));

        $stream->seek(0);
        $this->assertSame('01234', $stream->read(5));
    }

    /**
     * @depends testSeek
     */
    public function testGetContents()
    {
        $stream = new MemoryStream();
        $stream->write('0123456789');
        $stream->rewind();

        $this->assertSame('0123456789', $stream->getContents());

        $stream->seek(5);
        $this->assertSame('56789', $stream->getContents());
    }

    /**
     * @depends testGetContents
     */
    public function testToString()
    {
        $stream = new MemoryStream();
        $stream->write('0123456789');
        $stream->rewind();

        $this->assertSame('0123456789', (string)$stream);

        $stream->seek(5);
        $this->assertSame('0123456789', (string)$stream);
    }

    /**
     * @depends testRead
     */
    public function testGetSize()
    {
        $stream = new MemoryStream();

        $this->assertSame(0, $stream->getSize());

        $stream->write('0123456789');

        $this->assertSame(10, $stream->getSize());
    }

    /**
     * @depends testRead
     */
    public function testGetMetadata()
    {
        $stream = new MemoryStream();

        $metadata = $stream->getMetadata();

        $this->assertSame('rw', $metadata['mode']);
        $this->assertSame('rw', $stream->getMetadata('mode'));
    }

    /**
     * @depends testSeek
     */
    public function testRewrite()
    {
        $stream = new MemoryStream();
        $stream->write('0123456789');

        $stream->seek(5);
        $this->assertSame(4, $stream->write('0000'));

        $this->assertSame('0123400009', (string)$stream);
    }
}