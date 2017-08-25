<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\captcha;

use yii\captcha\GdDriver;
use yiiunit\TestCase;

class GdDriverTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        if (!extension_loaded('gd') || ($gdInfo = gd_info() && empty($gdInfo['FreeType Support']))) {
            $this->markTestSkipped('GD PHP extension with FreeType support is required.');
        }

        parent::setUp();
    }

    public function testRenderImage()
    {
        $driver = new GdDriver();
        $driver->width = 222;
        $driver->height = 111;

        $imageBinary = $driver->renderImage('test');
        $this->assertNotEmpty($imageBinary);

        $size = getimagesizefromstring($imageBinary);
        $this->assertEquals($driver->width, $size[0]);
        $this->assertEquals($driver->height, $size[1]);
        $this->assertEquals($driver->getImageMimeType(), $size['mime']);
    }
}