<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\http;

use Yii;
use yii\helpers\FileHelper;
use yii\http\ResourceStream;
use yiiunit\TestCase;

class ResourceStreamTest extends TestCase
{
    /**
     * @var string test file path.
     */
    protected $testFilePath;


    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->testFilePath = Yii::getAlias('@yiiunit/runtime') . DIRECTORY_SEPARATOR . 'resource-stream-test-' . getmypid();
        FileHelper::createDirectory($this->testFilePath);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        FileHelper::removeDirectory($this->testFilePath);
        parent::tearDown();
    }

    public function testRead()
    {
        $filename = $this->testFilePath . DIRECTORY_SEPARATOR . 'read.txt';
        file_put_contents($filename, '0123456789');

        $stream = new ResourceStream();
        $stream->resource = fopen($filename, 'r');

        $this->assertTrue($stream->isReadable());
        $this->assertTrue($stream->isSeekable());
        $this->assertFalse($stream->isWritable());

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
        $filename = $this->testFilePath . DIRECTORY_SEPARATOR . 'seek.txt';
        file_put_contents($filename, '0123456789');

        $stream = new ResourceStream();
        $stream->resource = fopen($filename, 'r');

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
        $filename = $this->testFilePath . DIRECTORY_SEPARATOR . 'get-content.txt';
        file_put_contents($filename, '0123456789');

        $stream = new ResourceStream();
        $stream->resource = fopen($filename, 'r');

        $this->assertSame('0123456789', $stream->getContents());

        $stream->seek(5);
        $this->assertSame('56789', $stream->getContents());
    }

    /**
     * @depends testGetContents
     */
    public function testToString()
    {
        $filename = $this->testFilePath . DIRECTORY_SEPARATOR . 'to-string.txt';
        file_put_contents($filename, '0123456789');

        $stream = new ResourceStream();
        $stream->resource = fopen($filename, 'r');

        $this->assertSame('0123456789', (string)$stream);

        $stream->seek(5);
        $this->assertSame('0123456789', (string)$stream);
    }

    /**
     * @depends testRead
     */
    public function testWrite()
    {
        $filename = $this->testFilePath . DIRECTORY_SEPARATOR . 'write.txt';

        $stream = new ResourceStream();
        $stream->resource = fopen($filename, 'w+');

        $this->assertTrue($stream->isWritable());

        $stream->write('01234');
        $stream->write('56789');

        $stream->close();

        $this->assertSame('0123456789', file_get_contents($filename));
    }

    /**
     * @depends testRead
     */
    public function testGetSize()
    {
        $filename = $this->testFilePath . DIRECTORY_SEPARATOR . 'get-size.txt';
        file_put_contents($filename, '0123456789');

        $stream = new ResourceStream();
        $stream->resource = fopen($filename, 'r');

        $this->assertSame(10, $stream->getSize());

        file_put_contents($filename, '');
        $this->assertSame(0, $stream->getSize());
    }

    /**
     * @depends testRead
     */
    public function testGetMetadata()
    {
        $filename = $this->testFilePath . DIRECTORY_SEPARATOR . 'get-meta-data.txt';
        file_put_contents($filename, '0123456789');

        $stream = new ResourceStream();
        $stream->resource = fopen($filename, 'r');

        $metadata = $stream->getMetadata();

        $this->assertSame('r', $metadata['mode']);
        $this->assertSame('plainfile', $metadata['wrapper_type']);

        $this->assertSame('r', $stream->getMetadata('mode'));
    }
}