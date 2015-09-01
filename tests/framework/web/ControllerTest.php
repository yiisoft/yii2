<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use Yii;
use yiiunit\TestCase;
use yii\web\Controller;
use yiiunit\framework\di\stubs\Qux;
use yiiunit\framework\di\stubs\QuxInterface;
use yii\base\InlineAction;
use yii\base\Object;

/**
 * @group web
 */
class ControllerTest extends TestCase
{

    protected function setUp()
    {
        $this->mockApplication([
            'components'=>[
                'barBelongApp'=>[
                    'class'=>  Bar::className(),
                    'foo'=>'belong_app'
                ],
                'quxApp'=>[
                    'class' => Qux::className(),
                    'a' => 'belong_app'
                ]
            ]
        ]);
    }

    public function testBindActionParams()
    {
        $controller = new FakeController('fake', Yii::$app);
        $aksi1 = new InlineAction('aksi1', $controller, 'actionAksi1');
        $aksi2 = new InlineAction('aksi2', $controller, 'actionAksi2');

        Yii::$container->set('yiiunit\framework\di\stubs\QuxInterface', [
            'class' => Qux::className(),
            'a' => 'mdm'
        ]);
        Yii::$container->set(Bar::className(),[
            'foo' => 'independent'
        ]);
        
        $params = ['fromGet'=>'from query params'];

        list($bar, $fromGet, $other) = $controller->bindActionParams($aksi1, $params);
        $this->assertTrue($bar instanceof Bar);
        $this->assertNotEquals($bar, Yii::$app->barBelongApp);
        $this->assertEquals('independent', $bar->foo);
        $this->assertEquals('from query params', $fromGet);
        $this->assertEquals('default', $other);

        list($barBelongApp, $qux, $quxApp) = $controller->bindActionParams($aksi2, $params);
        $this->assertTrue($barBelongApp instanceof Bar);
        $this->assertEquals($barBelongApp, Yii::$app->barBelongApp);
        $this->assertEquals('belong_app', $barBelongApp->foo);
        $this->assertTrue($qux instanceof QuxInterface);
        $this->assertEquals('mdm', $qux->a);
        $this->assertTrue($quxApp instanceof QuxInterface);
        $this->assertEquals('belong_app', $quxApp->a);
    }
}

class FakeController extends Controller
{
    public function actionAksi1(Bar $bar, $fromGet, $other='default')
    {
    }

    public function actionAksi2(Bar $barBelongApp, QuxInterface $qux, QuxInterface $quxApp)
    {
    }
}

class Bar extends Object
{
    public $foo;
}

