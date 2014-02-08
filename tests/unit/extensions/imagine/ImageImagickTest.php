<?php

namespace yiiunit\extensions\imagine;

use yii\imagine\Image;

/**
 * @group vendor
 * @group imagine
 */
class ImageImagickTest extends AbstractImageTest
{

	protected function setUp()
	{
		if (!class_exists('Imagick')) {
			$this->markTestSkipped('Skipping ImageImagickTest, Imagick is not installed');
		} else {
			$this->image = new Image();
			$this->image->setDriver(Image::DRIVER_IMAGICK);
			parent::setUp();
		}
	}

	protected function isFontTestSupported()
	{
		return true;
	}

}