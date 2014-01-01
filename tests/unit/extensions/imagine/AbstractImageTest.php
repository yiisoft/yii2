<?php
namespace yiiunit\extensions\imagine;

use Yii;
use yii\imagine\Image;
use Imagine\Image\Point;
use yiiunit\VendorTestCase;

Yii::setAlias('@yii/imagine', __DIR__ . '/../../../../extensions/yii/imagine');

abstract class AbstractImageTest extends VendorTestCase
{
	protected $imageFile;
	protected $watermarkFile;
	protected $runtimeTextFile;
	protected $runtimeWatermarkFile;

	protected function setUp()
	{
		$this->imageFile = Yii::getAlias('@yiiunit/data/imagine/large') . '.jpg';
		$this->watermarkFile = Yii::getAlias('@yiiunit/data/imagine/xparent') . '.gif';
		$this->runtimeTextFile = Yii::getAlias('@yiiunit/runtime/image-text-test') . '.png';
		$this->runtimeWatermarkFile = Yii::getAlias('@yiiunit/runtime/image-watermark-test') . '.png';
		parent::setUp();
	}

	protected function tearDown()
	{
		@unlink($this->runtimeTextFile);
		@unlink($this->runtimeWatermarkFile);
	}

	public function testText()
	{
		if (!$this->isFontTestSupported()) {
			$this->markTestSkipped('Skipping ImageGdTest Gd not installed');
		}

		$fontFile = Yii::getAlias('@yiiunit/data/imagine/GothamRnd-Light') . '.otf';

		$img = Image::text($this->imageFile, 'Yii-2 Image', [
			'font' => $fontFile,
			'size' => 12,
			'color' => '000'
		]);

		$img->save($this->runtimeTextFile);
		$this->assertTrue(file_exists($this->runtimeTextFile));

	}

	public function testCrop()
	{
		$point = [20, 20];
		$img = Image::crop($this->imageFile, 100, 100, $point);

		$this->assertEquals(100, $img->getSize()->getWidth());
		$this->assertEquals(100, $img->getSize()->getHeight());

		$point = new Point(20, 20);
		$img = Image::crop($this->imageFile, 100, 100, $point);
		$this->assertEquals(100, $img->getSize()->getWidth());
		$this->assertEquals(100, $img->getSize()->getHeight());

	}

	public function testWatermark()
	{
		$img = Image::watermark($this->imageFile, $this->watermarkFile);
		$img->save($this->runtimeWatermarkFile);
		$this->assertTrue(file_exists($this->runtimeWatermarkFile));
	}

	public function testFrame()
	{
		$frameSize = 5;
		$original = Image::getImagine()->open($this->imageFile);
		$originalSize = $original->getSize();
		$img = Image::frame($this->imageFile, $frameSize, '666', 0);
		$size = $img->getSize();

		$this->assertEquals($size->getWidth(), $originalSize->getWidth() + ($frameSize * 2));
	}

	public function testThumbnail()
	{
		$img = Image::thumbnail($this->imageFile, 120, 120);

		$this->assertEquals(120, $img->getSize()->getWidth());
		$this->assertEquals(120, $img->getSize()->getHeight());
	}

	/**
	 * @expectedException \yii\base\InvalidConfigException
	 */
	public function testShouldThrowExceptionOnDriverInvalidArgument()
	{
		Image::setImagine(null);
		Image::$driver = 'fake-driver';
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testShouldThrowExceptionOnCropInvalidArgument()
	{
		Image::crop($this->imageFile, 100, 100, new \stdClass());
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testShouldThrowExceptionOnWatermarkInvalidArgument()
	{
		Image::watermark($this->imageFile, $this->watermarkFile, new \stdClass());
	}


	abstract protected function isFontTestSupported();
}
