<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\widgets;

use yiiunit\TestCase;
use yii\widgets\ContentDecorator;

/**
 * @group widgets
 */
class ContentDecoratorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockWebApplication();
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/15536
     */
    public function testShouldTriggerInitEvent(): void
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
}
