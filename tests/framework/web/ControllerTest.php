<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use Yii;
use yiiunit\TestCase;
use yiiunit\framework\di\stubs\Qux;
use yiiunit\framework\web\stubs\Bar;
use yiiunit\framework\web\stubs\OtherQux;
use yii\base\InlineAction;

/**
 * @group web
 */
class ControllerTest extends TestCase
{

    public function testBindActionParams()
    {
        $this->mockApplication([
            'components'=>[
                'barBelongApp'=>[
                    'class'=>  Bar::className(),
                    'foo'=>'belong_app'
                ],
                'quxApp'=>[
                    'class' => OtherQux::className(),
                    'b' => 'belong_app'
                ]
            ]
        ]);

        $controller = new FakeController('fake', Yii::$app);
        $aksi1 = new InlineAction('aksi1', $controller, 'actionAksi1');
        $aksi2 = new InlineAction('aksi2', $controller, 'actionAksi2');
        $aksi3 = new InlineAction('aksi3', $controller, 'actionAksi3');

        Yii::$container->set('yiiunit\framework\di\stubs\QuxInterface', [
            'class' => Qux::className(),
            'a' => 'D426'
        ]);
        Yii::$container->set(Bar::className(),[
            'foo' => 'independent'
        ]);
        
        $params = ['fromGet'=>'from query params','q'=>'d426','validator'=>'avaliable'];

        list($bar, $fromGet, $other) = $controller->bindActionParams($aksi1, $params);
        $this->assertTrue($bar instanceof Bar);
        $this->assertNotEquals($bar, Yii::$app->barBelongApp);
        $this->assertEquals('independent', $bar->foo);
        $this->assertEquals('from query params', $fromGet);
        $this->assertEquals('default', $other);

        list($barBelongApp, $qux) = $controller->bindActionParams($aksi2, $params);
        $this->assertTrue($barBelongApp instanceof Bar);
        $this->assertEquals($barBelongApp, Yii::$app->barBelongApp);
        $this->assertEquals('belong_app', $barBelongApp->foo);
        $this->assertTrue($qux instanceof Qux);
        $this->assertEquals('D426', $qux->a);

        list($quxApp) = $controller->bindActionParams($aksi3, $params);
        $this->assertTrue($quxApp instanceof OtherQux);
        $this->assertEquals($quxApp, Yii::$app->quxApp);
        $this->assertEquals('belong_app', $quxApp->b);

        $result = $controller->runAction('aksi4', $params);
        $this->assertEquals(['independent', 'other_qux', 'd426'], $result);

        $result = $controller->runAction('aksi5', $params);
        $this->assertEquals(['d426', 'independent', 'other_qux'], $result);

        $result = $controller->runAction('aksi6', $params);
        $this->assertEquals(['d426', false, true], $result);
    }
}
