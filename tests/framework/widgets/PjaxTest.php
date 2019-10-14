<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
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
        $nonPjaxWidget1 = new ListView([ 'dataProvider' => new ArrayDataProvider()]);
        ob_start();
        $pjax1 = new Pjax();
        ob_end_clean();
        $nonPjaxWidget2 = new ListView(['dataProvider' => new ArrayDataProvider()]);
        ob_start();
        $pjax2 = new Pjax();
        ob_end_clean();

        $this->assertRegExp('/^w[a-z0-9]{8}0$/', $nonPjaxWidget1->options['id']);
        $this->assertRegExp('/^w[a-z0-9]{8}1$/', $nonPjaxWidget2->options['id']);
        $this->assertRegExp('/^p[a-z0-9]{8}0$/', $pjax1->options['id']);
        $this->assertRegExp('/^p[a-z0-9]{8}1$/', $pjax2->options['id']);
    }

    protected function setUp()
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
