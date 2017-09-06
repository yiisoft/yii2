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
use yiiunit\framework\web\stubs\ModelStub;
use yiiunit\framework\web\stubs\VendorImage;
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
        $this->generateFakeFiles();
    }

    private function generateFakeFileData()
    {
        return [
            'name' => md5(mt_rand()),
            'tmp_name' => md5(mt_rand()),
            'type' => 'image/jpeg',
            'size' => mt_rand(1000, 10000),
            'error' => 0,
        ];
    }

    private function generateFakeFiles()
    {
        $_FILES['ModelStub[prod_image]'] = $this->generateFakeFileData();
        $_FILES['ModelStub[prod_images][]'] = $this->generateFakeFileData();
        $_FILES['ModelStub[prod_images][]'] = $this->generateFakeFileData();
        $_FILES['ModelStub[prod_images][]'] = $this->generateFakeFileData();

        $_FILES['ModelStub[vendor_image]'] = $this->generateFakeFileData();
        $_FILES['ModelStub[vendor_images][]'] = $this->generateFakeFileData();
        $_FILES['ModelStub[vendor_images][]'] = $this->generateFakeFileData();
        $_FILES['ModelStub[vendor_images][]'] = $this->generateFakeFileData();
    }

    // Tests :

    public function testGetInstance()
    {
        $productImage = UploadedFile::getInstance(new ModelStub(), 'prod_image');
        $vendorImage = VendorImage::getInstance(new ModelStub(), 'vendor_image');

        $this->assertInstanceOf(UploadedFile::class, $productImage);
        $this->assertInstanceOf(VendorImage::class, $vendorImage);
    }

    public function testGetInstances()
    {
        $productImages = UploadedFile::getInstances(new ModelStub(), 'prod_images');
        $vendorImages = VendorImage::getInstances(new ModelStub(), 'vendor_images');

        foreach ($productImages as $productImage) {
            $this->assertInstanceOf(UploadedFile::class, $productImage);
        }

        foreach ($vendorImages as $vendorImage) {
            $this->assertInstanceOf(VendorImage::class, $vendorImage);
        }
    }

    public function testSetupStream()
    {
        $uploadedFile = new UploadedFile();

        $stream = new MemoryStream();
        $uploadedFile->setStream($stream);
        $this->assertSame($stream, $uploadedFile->getStream());

        $uploadedFile->setStream(['class' => MemoryStream::class]);
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
