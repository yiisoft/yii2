<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use yii\web\UploadedFile;
use yiiunit\framework\web\stubs\ModelStub;
use yiiunit\framework\web\stubs\VendorImage;
use yiiunit\TestCase;

/**
 * @group web
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

        $this->assertInstanceOf(UploadedFile::className(), $productImage);
        $this->assertInstanceOf(VendorImage::className(), $vendorImage);
    }

    public function testGetInstances()
    {
        $productImages = UploadedFile::getInstances(new ModelStub(), 'prod_images');
        $vendorImages = VendorImage::getInstances(new ModelStub(), 'vendor_images');

        foreach ($productImages as $productImage) {
            $this->assertInstanceOf(UploadedFile::className(), $productImage);
        }

        foreach ($vendorImages as $vendorImage) {
            $this->assertInstanceOf(VendorImage::className(), $vendorImage);
        }
    }
}
