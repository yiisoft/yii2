<?php

namespace yiiunit\extensions\imagine;

use yii\imagine\Image;

/**
 * @group vendor
 * @group imagine
 */
class ImageGmagickTest extends AbstractImageTest
{

	protected function setUp()
	{
		if (!class_exists('Gmagick')) {
			$this->markTestSkipped('Skipping ImageGmagickTest, Gmagick is not installed');
		} else {
			$this->image = new Image();
			$this->image->setDriver(Image::DRIVER_GMAGICK);
			parent::setUp();
		}
	}

	protected function isFontTestSupported()
	{
		return true;
	}

}