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
        $nonPjaxWidget1 = new ListView(['dataProvider' => new ArrayDataProvider()]);
        ob_start();
        $pjax1 = new Pjax();
        ob_end_clean();
        $nonPjaxWidget2 = new ListView(['dataProvider' => new ArrayDataProvider()]);
        ob_start();
        $pjax2 = new Pjax();
        ob_end_clean();

        $this->assertSame('w0', $nonPjaxWidget1->options['id']);
        $this->assertSame('w1', $nonPjaxWidget2->options['id']);
        $this->assertSame('p0', $pjax1->options['id']);
        $this->assertSame('p1', $pjax2->options['id']);
    }

    protected function setUp()
    {
        parent::setUp();
        $this->mockWebApplication();
    }
}
