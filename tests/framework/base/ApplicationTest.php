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
                    Dispatcher::className() => DispatcherMock::className()
                ]
            ],
            'bootstrap' => ['log']
        ]);

        $this->assertInstanceOf(DispatcherMock::className(), Yii::$app->log);
    }
}

class DispatcherMock extends Dispatcher
{

}
