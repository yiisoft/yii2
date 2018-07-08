<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\http;

use Psr\Http\Message\StreamInterface;
use Yii;
use yii\http\FileStream;
use yii\http\MemoryStream;
use yii\http\UploadedFile;
use yiiunit\TestCase;

/**
 * @group http
 */
class UploadedFileTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    // Tests :

    public function testSetupStream()
    {
        $uploadedFile = new UploadedFile();

        $stream = new MemoryStream();
        $uploadedFile->setStream($stream);
        $this->assertSame($stream, $uploadedFile->getStream());

        $uploadedFile->setStream(['__class' => MemoryStream::class]);
        $this->assertNotSame($stream, $uploadedFile->getStream());
        $this->assertTrue($uploadedFile->getStream() instanceof MemoryStream);

        $uploadedFile->setStream(function () {
            return new FileStream(['filename' => 'test.txt']);
        });
        $this->assertTrue($uploadedFile->getStream() instanceof FileStream);
        $this->assertSame('test.txt', $uploadedFile->getStream()->filename);
    }

    /**
     * @depends testSetupStream
     */
    public function testDefaultStream()
    {
        $uploadedFile = new UploadedFile();
        $uploadedFile->setError(UPLOAD_ERR_OK);
        $uploadedFile->tempFilename = tempnam(Yii::getAlias('@yiiunit/runtime'), 'tmp-');
        file_put_contents($uploadedFile->tempFilename, '0123456789');

        $stream = $uploadedFile->getStream();
        $this->assertTrue($stream instanceof StreamInterface);
        $this->assertSame('0123456789', $stream->__toString());
    }
}
