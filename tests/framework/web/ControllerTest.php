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
        $this->mockApplication();

        $controller = new FakeController('fake', Yii::$app);
        $aksi1 = new InlineAction('aksi1', $controller, 'actionAksi1');

        $params = ['fromGet'=>'from query params','q'=>'d426','validator'=>'avaliable'];
        list($fromGet, $other) = $controller->bindActionParams($aksi1, $params);
        $this->assertEquals('from query params', $fromGet);
        $this->assertEquals('default', $other);

        $params = ['fromGet'=>'from query params','q'=>'d426','other'=>'avaliable'];
        list($fromGet, $other) = $controller->bindActionParams($aksi1, $params);
        $this->assertEquals('from query params', $fromGet);
        $this->assertEquals('avaliable', $other);

    }
}
