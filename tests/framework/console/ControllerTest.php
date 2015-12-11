<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\console;

use Yii;
use yiiunit\TestCase;
use yiiunit\framework\di\stubs\Qux;
use yiiunit\framework\web\stubs\Bar;
use yiiunit\framework\web\stubs\OtherQux;

/**
 * @group console
 */
class ControllerTest extends TestCase
{

    public function testBindActionParams()
    {
        $this->mockApplication([
            'components' => [
                'barBelongApp' => [
                    'class' => Bar::className(),
                    'foo' => 'belong_app'
                ],
                'quxApp' => [
                    'class' => OtherQux::className(),
                    'b' => 'belong_app'
                ]
            ]
        ]);

        $controller = new FakeController('fake', Yii::$app);

        Yii::$container->set('yiiunit\framework\di\stubs\QuxInterface', [
            'class' => Qux::className(),
            'a' => 'D426'
        ]);
        Yii::$container->set(Bar::className(), [
            'foo' => 'independent'
        ]);

        $params = ['from params'];
        list($bar, $fromParam, $other) = $controller->run('aksi1', $params);
        $this->assertTrue($bar instanceof Bar);
        $this->assertNotEquals($bar, Yii::$app->barBelongApp);
        $this->assertEquals('independent', $bar->foo);
        $this->assertEquals('from params', $fromParam);
        $this->assertEquals('default', $other);

        $params = [];
        list($barBelongApp, $qux) = $controller->run('aksi2', $params);
        $this->assertTrue($barBelongApp instanceof Bar);
        $this->assertEquals($barBelongApp, Yii::$app->barBelongApp);
        $this->assertEquals('belong_app', $barBelongApp->foo);
        $this->assertTrue($qux instanceof Qux);
        $this->assertEquals('D426', $qux->a);

        $params = [];
        list($quxApp) = $controller->run('aksi3', $params);
        $this->assertTrue($quxApp instanceof OtherQux);
        $this->assertEquals($quxApp, Yii::$app->quxApp);
        $this->assertEquals('belong_app', $quxApp->b);

        $params = ['d426,mdmunir', 'single'];
        $result = $controller->runAction('aksi4', $params);
        $this->assertEquals(['independent', 'other_qux', ['d426', 'mdmunir'], 'single'], $result);

        $params = ['d426'];
        $result = $controller->runAction('aksi5', $params);
        $this->assertEquals(['d426', 'independent', 'other_qux'], $result);

        $params = ['mdmunir'];
        $result = $controller->runAction('aksi6', $params);
        $this->assertEquals(['mdmunir', false, true], $result);

        $params = ['arg1', 'arg2', 'arg3'];
        $result = $controller->runAction('aksi8', $params);
        $this->assertEquals($params, $result);

        $params = ['arg1', 'arg2', 'arg3'];
        $result = $controller->runAction('aksi9', $params);
        $this->assertEquals(['arg1', 'arg2', Yii::$app->quxApp, 'arg3'], $result);

        $params = ['avaliable'];
        $message = Yii::t('yii', 'Missing required arguments: {params}', ['params' => implode(', ', ['missing'])]);
        $this->setExpectedException('yii\console\Exception', $message);
        $result = $controller->runAction('aksi7', $params);

    }
}
