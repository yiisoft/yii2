<?php

namespace yiiunit\framework\widgets;

use yii\data\ArrayDataProvider;
use yii\widgets\ListView;
use yii\widgets\Pjax;
use yiiunit\TestCase;

class PjaxTest extends TestCase
{
    public function testGeneratedIdByPjaxWidget()
    {
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
        $this->assertEquals('w_pjax_0', $pjax1->options['id']);
        $this->assertEquals('w_pjax_1', $pjax2->options['id']);
    }

    protected function setUp()
    {
        parent::setUp();
        $this->mockWebApplication();
    }

}
