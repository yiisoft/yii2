<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base;

use yii\base\Behavior;
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

    /**
     * @depends testWidget
     */
    public function testGarbageCollection()
    {
        TestWidget::widget([
            'id' => 'test',
            'as test' => [
                'class' => TestWidgetBehavior::className()
            ],
        ]);
        $this->assertSame(0, TestWidget::$instanceCount);
    }
}

class TestWidget extends Widget
{
    public static $instanceCount = 0;

    public function __construct($config = [])
    {
        parent::__construct($config);
        static::$instanceCount++;
    }

    public function __destruct()
    {
        static::$instanceCount--;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        return '<run-' . $this->id . '>';
    }
}

class TestWidgetBehavior extends Behavior
{
    public function events()
    {
        return [
            Widget::EVENT_BEFORE_RUN => 'beforeRun'
        ];
    }

    public function beforeRun(WidgetEvent $event)
    {
        // blank
    }
}