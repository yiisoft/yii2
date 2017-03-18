<?php

namespace yii\captcha;

use yiiunit\TestCase;
use yii\captcha\ImageExtentionModel;
use yii\base\InvalidConfigException;

class ImageExtentionModelTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->mockApplication();
    }

    public function testGetImageExtensionReturnImagick()
    {
        $imageModel = $this->getMockBuilder(ImageExtentionModel::className())
            ->setMethods(['getLoadedExtensions', 'existsPNGImagickFormats'])
            ->getMock();
        $imageModel->expects($this->any())
            ->method('getLoadedExtensions')
            ->willReturn([ImageExtentionModel::IMAGICK]);
        $imageModel->expects($this->any())
            ->method('existsPNGImagickFormats')
            ->willReturn(true);

        $extension = $imageModel->getImageExtension();

        $this->assertEquals(ImageExtentionModel::IMAGICK, $extension);
    }

    public function testGetImageExtensionReturnGD()
    {
        $imageModel = $this->getMockBuilder(ImageExtentionModel::className())
            ->setMethods(['getLoadedExtensions', 'isFreeTypeSupportGD'])
            ->getMock();

        $imageModel->expects($this->any())
            ->method('getLoadedExtensions')
            ->willReturn([ImageExtentionModel::GD]);

        $imageModel->expects($this->any())
            ->method('isFreeTypeSupportGD')
            ->willReturn(true);

        $extension = $imageModel->getImageExtension();

        $this->assertEquals(ImageExtentionModel::GD, $extension);
    }

    public function testGetImageExtensionGenerateInvalidConfigException()
    {
        $imageModel = $this->getMockBuilder(ImageExtentionModel::className())
            ->setMethods(['getLoadedExtensions'])
            ->getMock();

        $imageModel->expects($this->any())
            ->method('getLoadedExtensions')
            ->willReturn([]);


        $this->setExpectedException(InvalidConfigException::class);

        $imageModel->getImageExtension();
    }

    public function testExistsPNGImagickFormatSupported()
    {
        $imageModel = $this->getMockBuilder(ImageExtentionModel::className())
            ->setMethods(['getImagickFormats'])
            ->getMock();

        $imageModel->expects($this->any())
            ->method('getImagickFormats')
            ->willReturn(['PNG']);

        $pngFormatSupported = $this->invokeMethod($imageModel, 'existsPNGImagickFormats');

        $this->assertTrue($pngFormatSupported);
    }

    public function testExistsPNGImagickFormatNotSupported()
    {
        $imageModel = $this->getMockBuilder(ImageExtentionModel::className())
            ->setMethods(['getImagickFormats'])
            ->getMock();

        $imageModel->expects($this->any())
            ->method('getImagickFormats')
            ->willReturn(['JPEG']);

        $pngFormatSupported = $this->invokeMethod($imageModel, 'existsPNGImagickFormats');

        $this->assertFalse($pngFormatSupported);
    }

    public function testIsFreeTypeSupportGDSupported()
    {
        $imageModel = $this->getMockBuilder(ImageExtentionModel::className())
            ->setMethods(['getGDInfo'])
            ->getMock();

        $imageModel->expects($this->any())
            ->method('getGDInfo')
            ->willReturn(['FreeType Support']);

        $isFreeTypeSupported = $this->invokeMethod($imageModel, 'isFreeTypeSupportGD');

        $this->assertTrue($isFreeTypeSupported);
    }

    public function testIsFreeTypeSupportGDNotSupported()
    {
        $imageModel = $this->getMockBuilder(ImageExtentionModel::className())
            ->setMethods(['getGDInfo'])
            ->getMock();

        $imageModel->expects($this->any())
            ->method('getGDInfo')
            ->willReturn([]);

        $isFreeTypeSupported = $this->invokeMethod($imageModel, 'isFreeTypeSupportGD');

        $this->assertFalse($isFreeTypeSupported);
    }
}
