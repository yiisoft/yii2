<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base;

use Yii;
use yii\base\Widget;
use yii\base\WidgetEvent;
use yii\di\Container;
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
     * @see https://github.com/yiisoft/yii2/issues/19030
     */
    public function testDependencyInjection()
    {
        Yii::$container = new Container();
        Yii::$container->setDefinitions([
            TestWidgetB::className() => [
                'class' => TestWidget::className()
            ]
        ]);

        ob_start();
        ob_implicit_flush(false);

        $widget = TestWidgetB::begin(['id' => 'test']);
        $this->assertTrue($widget instanceof TestWidget);
        TestWidgetB::end();

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
     * @depends testBeginEnd
     */
    public function testStackTrackingDisorder()
    {
        $this->expectException('yii\base\InvalidCallException');
        TestWidgetA::begin();
        TestWidgetB::begin();
        TestWidgetA::end();
        TestWidgetB::end();
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

class TestWidgetA extends Widget
{
    public static $stack = [];
}

class TestWidgetB extends Widget
{
}
