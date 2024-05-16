<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\widgets;

use yii\data\ArrayDataProvider;
use yii\widgets\ListView;
use yii\widgets\Pjax;
use yiiunit\TestCase;

class PjaxTest extends TestCase
{
    public function testGeneratedIdByPjaxWidget()
    {
        ListView::$counter = 0;
        Pjax::$counter = 0;
        $nonPjaxWidget1 = new ListView(['dataProvider' => new ArrayDataProvider()]);
        ob_start();
        $pjax1 = new Pjax();
        ob_end_clean();
        $nonPjaxWidget2 = new ListView(['dataProvider' => new ArrayDataProvider()]);
        ob_start();
        $pjax2 = new Pjax();
        ob_end_clean();

        $this->assertEquals('w0', $nonPjaxWidget1->options['id']);
        $this->assertEquals('w1', $nonPjaxWidget2->options['id']);
        $this->assertEquals('p0', $pjax1->options['id']);
        $this->assertEquals('p1', $pjax2->options['id']);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockWebApplication();
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/15536
     */
    public function testShouldTriggerInitEvent()
    {
        $initTriggered = false;
        ob_start();
        $pjax = new Pjax(
            [
                'on init' => function () use (&$initTriggered) {
                    $initTriggered = true;
                }
            ]
        );
        ob_end_clean();
        $this->assertTrue($initTriggered);
    }
}
