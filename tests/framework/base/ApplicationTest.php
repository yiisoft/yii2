<?php

namespace yiiunit\framework\base;

use Yii;
use yii\log\Dispatcher;
use yiiunit\TestCase;

/**
 * @group base
 */
class ApplicationTest extends TestCase
{
    public function testContainerSettingsAffectBootstrap()
    {
        $this->mockApplication([
            'container' => [
                'definitions' => [
                    Dispatcher::class => DispatcherMock::class
                ]
            ],
            'bootstrap' => ['log']
        ]);

        $this->assertInstanceOf(DispatcherMock::class, Yii::$app->log);
    }
}

class DispatcherMock extends Dispatcher
{

}
