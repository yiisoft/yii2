<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\http;

use Yii;
use yii\helpers\FileHelper;
use yii\http\FileStream;
use yiiunit\TestCase;

class FileStreamTest extends TestCase
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
        $this->testFilePath = Yii::getAlias('@yiiunit/runtime') . DIRECTORY_SEPARATOR . 'file-stream-test-' . getmypid();
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

        $stream = new FileStream();
        $stream->filename = $filename;
        $stream->mode = 'r';

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

        $stream = new FileStream();
        $stream->filename = $filename;
        $stream->mode = 'r';

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

        $stream = new FileStream();
        $stream->filename = $filename;
        $stream->mode = 'r';

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

        $stream = new FileStream();
        $stream->filename = $filename;
        $stream->mode = 'r';

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

        $stream = new FileStream();
        $stream->filename = $filename;
        $stream->mode = 'w+';

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

        $stream = new FileStream();
        $stream->filename = $filename;
        $stream->mode = 'r';

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

        $stream = new FileStream();
        $stream->filename = $filename;
        $stream->mode = 'r';

        $metadata = $stream->getMetadata();

        $this->assertSame('r', $metadata['mode']);
        $this->assertSame('plainfile', $metadata['wrapper_type']);

        $this->assertSame('r', $stream->getMetadata('mode'));
    }

    /**
     * @return array test data.
     */
    public function dataProviderFileMode()
    {
        return [
            ['r', true, false],
            ['r+', true, true],
            ['w', false, true],
            ['w+', true, true],
            ['rw', true, true],
            ['x', false, true],
            ['x+', true, true],
            ['c', false, true],
            ['c+', true, true],
            ['a', false, true],
            ['a+', true, true],
            ['wb', false, true],
            ['rb', true, false],
            ['w+b', true, true],
            ['r+b', true, true],
            ['rt', true, false],
            ['w+t', true, true],
            ['r+t', true, true],
            ['x+t', true, true],
            ['c+t', true, true],
        ];
    }

    /**
     * @depends testGetMetadata
     * @dataProvider dataProviderFileMode
     *
     * @param string $mode
     * @param bool $isReadable
     * @param bool $isWritable
     */
    public function testIsReadable($mode, $isReadable, $isWritable)
    {
        /* @var $stream FileStream|\PHPUnit_Framework_MockObject_MockObject */
        $stream = $this->getMockBuilder(FileStream::class)
            ->setMethods(['getMetadata'])
            ->getMock();

        $stream->expects($this->any())
            ->method('getMetadata')
            ->with('mode')
            ->willReturn($mode);

        $this->assertSame($isReadable, $stream->isReadable());
    }

    /**
     * @depends testGetMetadata
     * @dataProvider dataProviderFileMode
     *
     * @param string $mode
     * @param bool $isReadable
     * @param bool $isWritable
     */
    public function testIsWritable($mode, $isReadable, $isWritable)
    {
        /* @var $stream FileStream|\PHPUnit_Framework_MockObject_MockObject */
        $stream = $this->getMockBuilder(FileStream::class)
            ->setMethods(['getMetadata'])
            ->getMock();

        $stream->expects($this->any())
            ->method('getMetadata')
            ->with('mode')
            ->willReturn($mode);

        $this->assertSame($isWritable, $stream->isWritable());
    }
}