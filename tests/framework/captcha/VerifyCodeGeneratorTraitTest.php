<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\captcha;

use yii\captcha\Driver;
use yiiunit\TestCase;

class VerifyCodeGeneratorTraitTest extends TestCase
{
    public function testGenerateVerifyCode()
    {
        /* @var $driver Driver */
        $driver = $this->getMockBuilder(Driver::class)
            ->getMockForAbstractClass();

        $this->assertNotEmpty($driver->generateVerifyCode());

        $driver->minLength = 10;
        $driver->maxLength = 10;
        $this->assertEquals(10, strlen($driver->generateVerifyCode()));
    }
}