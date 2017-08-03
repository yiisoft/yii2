<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base;

use Yii;
use yii\base\BootstrapInterface;
use yii\base\Component;
use yii\base\Module;
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
                    Dispatcher::className() => DispatcherMock::className(),
                ],
            ],
            'bootstrap' => ['log'],
        ]);

        $this->assertInstanceOf(DispatcherMock::className(), Yii::$app->log);
    }

    public function testBootstrap()
    {
        Yii::getLogger()->flush();


        $this->mockApplication([
            'components' => [
                'withoutBootstrapInterface' => [
                    'class' => Component::className()
                ],
                'withBootstrapInterface' => [
                    'class' => BootstrapComponentMock::className()
                ]
            ],
            'modules' => [
                'moduleX' => [
                    'class' => Module::className()
                ]
            ],
            'bootstrap' => [
                'withoutBootstrapInterface',
                'withBootstrapInterface',
                'moduleX',
                function () {
                }

            ],
        ]);
        $this->assertSame('Bootstrap with yii\base\Component', Yii::getLogger()->messages[0][0]);
        $this->assertSame('Bootstrap with yiiunit\framework\base\BootstrapComponentMock::bootstrap()', Yii::getLogger()->messages[1][0]);
        $this->assertSame('Loading module: moduleX', Yii::getLogger()->messages[2][0]);
        $this->assertSame('Bootstrap with yii\base\Module', Yii::getLogger()->messages[3][0]);
        $this->assertSame('Bootstrap with Closure', Yii::getLogger()->messages[4][0]);
    }
}

class DispatcherMock extends Dispatcher
{
}

class BootstrapComponentMock extends Component implements BootstrapInterface
{
    public function bootstrap($app)
    {
    }
}