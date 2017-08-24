<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base;

use Yii;
use yii\base\Controller;
use yiiunit\TestCase;

/**
 * @group base
 */
class ControllerTest extends TestCase
{
    public static $actionRuns = [];

    public function testRunAction()
    {
        $this->mockApplication();

        static::$actionRuns = [];
        $controller = new TestController('test-controller', Yii::$app);
        $this->assertNull($controller->action);
        $result = $controller->runAction('test1');
        $this->assertSame('test1', $result);
        $this->assertSame([
            'test-controller/test1',
        ], static::$actionRuns);
        $this->assertNotNull($controller->action);
        $this->assertSame('test1', $controller->action->id);
        $this->assertSame('test-controller/test1', $controller->action->uniqueId);

        $result = $controller->runAction('test2');
        $this->assertSame('test2', $result);
        $this->assertSame([
            'test-controller/test1',
            'test-controller/test2',
        ], static::$actionRuns);
        $this->assertNotNull($controller->action);
        $this->assertSame('test1', $controller->action->id);
        $this->assertSame('test-controller/test1', $controller->action->uniqueId);
    }
}


class TestController extends Controller
{
    public function actionTest1()
    {
        ControllerTest::$actionRuns[] = $this->action->uniqueId;
        return 'test1';
    }
    public function actionTest2()
    {
        ControllerTest::$actionRuns[] = $this->action->uniqueId;
        return 'test2';
    }
}
