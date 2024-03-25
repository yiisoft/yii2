<?php

namespace yiiunit\framework\behaviors;

use PHPUnit_Framework_MockObject_MockObject;
use yii\base\Widget;
use yii\behaviors\CacheableWidgetBehavior;
use yiiunit\TestCase;

/**
 * Unit test for [[\yii\behaviors\CacheableWidgetBehavior]].
 *
 * @see CacheableWidgetBehavior
 * @group behaviors
 */
class CacheableWidgetBehaviorTest extends TestCase
{
    /**
     * Default-initialized simple cacheable widget mock.
     *
     * @var PHPUnit_Framework_MockObject_MockObject|SimpleCacheableWidget|CacheableWidgetBehavior
     */
    private $simpleWidget;

    /**
     * Default-initialized dynamic cacheable widget mock.
     *
     * @var PHPUnit_Framework_MockObject_MockObject|DynamicCacheableWidget|CacheableWidgetBehavior
     */
    private $dynamicWidget;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->initializeApplicationMock();
        $this->initializeWidgetMocks();
    }

    /**
     * @throws \Exception
     */
    public function testWidgetIsRunWhenCacheIsEmpty()
    {
        $this->simpleWidget
            ->expects($this->once())
            ->method('run');

        $contents = $this->simpleWidget->test();
        $this->assertEquals('contents', $contents);
    }

    /**
     * @throws \Exception
     */
    public function testWidgetIsNotRunWhenCacheIsNotEmpty()
    {
        $this->simpleWidget->cacheDuration = 0;
        $this->simpleWidget
            ->expects($this->once())
            ->method('run');

        for ($counter = 0; $counter <= 1; $counter++) {
            $this->assertEquals('contents', $this->simpleWidget->test());
        }
    }

    /**
     * @throws \Exception
     */
    public function testDynamicContent()
    {
        $this->dynamicWidget->cacheDuration = 0;
        $this->dynamicWidget
            ->expects($this->once())
            ->method('run');

        for ($counter = 0; $counter <= 1; $counter++) {
            $expectedContents = sprintf('<div>dynamic contents: %d</div>', $counter);
            $this->assertEquals($expectedContents, $this->dynamicWidget->test());
        }
    }

    /**
     * Initializes a mock application.
     *
     */
    private function initializeApplicationMock()
    {
        $this->mockApplication([
            'components' => [
                'cache' => [
                    'class' => '\yii\caching\ArrayCache',
                ],
            ],
            'params' => [
                // Counter for dynamic contents testing.
                'counter' => 0,
            ],
        ]);
    }

    /**
     * Initializes mock widgets.
     *
     */
    private function initializeWidgetMocks()
    {
        $this->simpleWidget = $this->getWidgetMock(SimpleCacheableWidget::className());
        $this->dynamicWidget = $this->getWidgetMock(DynamicCacheableWidget::className());
    }

    /**
     * Returns a widget mock.
     * @param $widgetClass
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getWidgetMock($widgetClass)
    {
        $widgetMock = $this->getMockBuilder($widgetClass)
            ->setMethods(['run'])
            ->enableOriginalConstructor()
            ->enableProxyingToOriginalMethods()
            ->getMock();

        return $widgetMock;
    }
}

class BaseCacheableWidget extends Widget
{
    /**
     * {@inheritdoc}
     */
    public function test()
    {
        ob_start();
        ob_implicit_flush(false);
        try {
            $out = '';
            if ($this->beforeRun()) {
                $result = $this->run();
                $out = $this->afterRun($result);
            }
        } catch (\Exception $exception) {
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            throw $exception;
        }

        return ob_get_clean() . $out;
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'cacheable' => 'yii\behaviors\CacheableWidgetBehavior',
        ];
    }
}

class SimpleCacheableWidget extends BaseCacheableWidget
{
    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $content = 'contents';

        return $content;
    }
}

class DynamicCacheableWidget extends BaseCacheableWidget
{
    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $dynamicContentsExpression = 'return "dynamic contents: " . \Yii::$app->params["counter"]++;';
        $dynamicContents = $this->view->renderDynamic($dynamicContentsExpression);
        $content = '<div>' . $dynamicContents . '</div>';

        return $content;
    }
}
