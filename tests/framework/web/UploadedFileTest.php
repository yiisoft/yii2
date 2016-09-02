<?php

namespace yiiunit\framework\web;

use yii\base\Model;
use yiiunit\TestCase;
use yii\web\UploadedFile;
use yiiunit\framework\web\stubs\ProductImage;
use yiiunit\framework\web\stubs\VendorImage;
use yiiunit\framework\web\stubs\ImageModel;

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

    public function testGetInstance()
    {
        $productImage = ProductImage::getInstance(new ImageModel(), 'prod_image');
        $vendorImage = VendorImage::getInstance(new ImageModel(), 'vendor_image');

        $this->assertInstanceOf('\yiiunit\framework\web\stubs\ProductImage', $productImage);
        $this->assertInstanceOf('\yiiunit\framework\web\stubs\VendorImage', $vendorImage);
    }

    public function testGetInstances()
    {
        $productImages = ProductImage::getInstances(new ImageModel(), 'prod_images');
        $vendorImages = VendorImage::getInstances(new ImageModel(), 'vendor_images');

        foreach ($productImages as $productImage) {
            $this->assertInstanceOf('\yiiunit\framework\web\stubs\ProductImage', $productImage);
        }

        foreach ($vendorImages as $vendorImage) {
            $this->assertInstanceOf('\yiiunit\framework\web\stubs\VendorImage', $vendorImage);
        }
    }

    private function generateFakeFileData()
    {
        return [
            'name' => md5(mt_rand()),
            'tmp_name' => md5(mt_rand()),
            'type' => 'image/jpeg',
            'size' => mt_rand(1000, 10000),
            'error' => 0
        ];
    }

    private function generateFakeFiles()
    {
        $_FILES['ImageModel[prod_image]'] = $this->generateFakeFileData();
        $_FILES['ImageModel[prod_images][]'] = $this->generateFakeFileData();
        $_FILES['ImageModel[prod_images][]'] = $this->generateFakeFileData();
        $_FILES['ImageModel[prod_images][]'] = $this->generateFakeFileData();

        $_FILES['ImageModel[vendor_image]'] = $this->generateFakeFileData();
        $_FILES['ImageModel[vendor_images][]'] = $this->generateFakeFileData();
        $_FILES['ImageModel[vendor_images][]'] = $this->generateFakeFileData();
        $_FILES['ImageModel[vendor_images][]'] = $this->generateFakeFileData();
    }
}
