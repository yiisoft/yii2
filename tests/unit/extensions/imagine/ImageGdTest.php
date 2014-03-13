<?php

namespace yiiunit\extensions\imagine;

use yii\imagine\Image;

/**
 * @group vendor
 * @group imagine
 */
class ImageGdTest extends AbstractImageTest
{
    protected function setUp()
    {
        if (!function_exists('gd_info')) {
            $this->markTestSkipped('Skipping ImageGdTest, Gd not installed');
        } else {
            Image::setImagine(null);
            Image::$driver = Image::DRIVER_GD2;
            parent::setUp();
        }
    }

    protected function isFontTestSupported()
    {
        $infos = gd_info();

        return isset($infos['FreeType Support']) ? $infos['FreeType Support'] : false;
    }
}
