<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\widgets;

use yii\widgets\ContentDecorator;

/**
 * @group widgets
 */
class ContentDecoratorTest extends \yiiunit\TestCase
{
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

        $contentDecorator = new ContentDecorator(
            [
                'viewFile' => '@app/views/layouts/base.php',
                'on init' => function () use (&$initTriggered) {
                    $initTriggered = true;
                }
            ]
        );

        ob_get_clean();

        $this->assertTrue($initTriggered);
    }

    public function testAfterRunResultNotEmpty()
    {
        $result = null;

        ob_start();
        ContentDecorator::begin([
            'viewFile' => '@yiiunit/data/views/layout.php',
            'on afterRun' => function ($event) use (&$result) {
                $result = $event->result;
            },
        ]);

        echo 'The Content';

        ContentDecorator::end();
        ob_end_clean();

        $this->assertContains('The Content', $result);
        $this->assertContains('<title>Test</title>', $result);
    }
}
