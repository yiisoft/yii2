<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base;

use yii\base\Widget;
use yii\base\WidgetEvent;
use yiiunit\TestCase;

/**
 * @group base
 */
class WidgetTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        parent::tearDown();
        Widget::$counter = 0;
        Widget::$stack = [];
    }

    public function testWidget()
    {
        $output = TestWidget::widget(['id' => 'test']);
        $this->assertSame('<run-test>', $output);
    }

    public function testBeginEnd()
    {
        ob_start();
        ob_implicit_flush(false);

        $widget = TestWidget::begin(['id' => 'test']);
        $this->assertTrue($widget instanceof TestWidget);
        TestWidget::end();

        $output = ob_get_clean();

        $this->assertSame('<run-test>', $output);
    }

    /**
     * @depends testBeginEnd
     */
    public function testStackTracking()
    {
        $this->expectException('yii\base\InvalidCallException');
        TestWidget::end();
    }

    /**
     * @depends testWidget
     */
    public function testEvents()
    {
        $output = TestWidget::widget([
            'id' => 'test',
            'on init' => function ($event) {
                echo '<init>';
            },
            'on beforeRun' => function (WidgetEvent $event) {
                echo '<before-run>';
            },
            'on afterRun' => function (WidgetEvent $event) {
                $event->result .= '<after-run>';
            },
        ]);
        $this->assertSame('<init><before-run><run-test><after-run>', $output);
    }

    /**
     * @depends testEvents
     */
    public function testPreventRun()
    {
        $output = TestWidget::widget([
            'id' => 'test',
            'on beforeRun' => function (WidgetEvent $event) {
                $event->isValid = false;
            },
        ]);
        $this->assertSame('', $output);
    }
}

class TestWidget extends Widget
{
    /**
     * {@inheritdoc}
     */
    public function run()
    {
        return '<run-' . $this->id . '>';
    }
}
