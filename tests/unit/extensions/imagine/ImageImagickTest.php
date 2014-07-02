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
        } elseif (defined('HHVM_VERSION')) {
            $this->markTestSkipped('Imagine does not seem to support HHVM right now.');
        } else {
            Image::setImagine(null);
            Image::$driver = Image::DRIVER_IMAGICK;
            parent::setUp();
        }
    }

    protected function isFontTestSupported()
    {
        return true;
    }
}
