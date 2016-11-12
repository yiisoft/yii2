<?php

namespace yiiunit\framework\widgets;

use Yii;
use yii\web\Request;
use yii\widgets\Pjax;

/**
 * @group widgets
 */
class PjaxTest extends \yiiunit\TestCase
{
    public function testSetDefaultIdByTrace()
    {
        $this->expectOutputRegex('~<div id="[a-zA-Z0-9]{32}".*>~');
        Pjax::begin();
    }

    public function testCustomId()
    {
        $this->expectOutputRegex('~<div id="custom-id".*>~');
        Pjax::begin([
            'id' => 'custom-id'
        ]);
    }

    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
        Yii::$app->set('request', new Request());
    }
}
