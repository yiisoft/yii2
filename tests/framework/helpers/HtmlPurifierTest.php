<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\helpers;

use yii\helpers\HtmlPurifier;
use yiiunit\TestCase;

/**
 * @group html-purifier
 */
class HtmlPurifierTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        if (!class_exists(\HTMLPurifier_Config::class)) {
            $this->markTestSkipped('"ezyang/htmlpurifier" package required');
            return;
        }

        parent::setUp();
        $this->mockApplication();
    }

    /**
     * Data provider for [[testProcess()]]
     * @return array test data.
     */
    public function dataProviderProcess()
    {
        return [
            ['Some <b>html</b>', 'Some <b>html</b>'],
            ['Some script<script>alert("!")</script>', 'Some script'],
        ];
    }

    /**
     * @dataProvider dataProviderProcess
     *
     * @param string $content
     * @param string $expectedResult
     */
    public function testProcess($content, $expectedResult)
    {
        $this->assertSame($expectedResult, HtmlPurifier::process($content));
    }
}