<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base;

use Yii;
use yii\base\Action;
use yii\base\ActionFilter;
use yii\base\Controller;
use yii\web\User;
use yiiunit\TestCase;

/**
 * @group base
 */
class ActionFilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testFilter()
    {
        // no filters
        $controller = new FakeController('fake', Yii::$app);
        $this->assertNull($controller->result);
        $result = $controller->runAction('test');
        $this->assertEquals('x', $result);
        $this->assertNull($controller->result);

        // all filters pass
        $controller = new FakeController('fake', Yii::$app, [
            'behaviors' => [
                'filter1' => Filter1::className(),
                'filter3' => Filter3::className(),
            ],
        ]);
        $this->assertNull($controller->result);
        $result = $controller->runAction('test');
        $this->assertEquals('x-3-1', $result);
        $this->assertEquals([1, 3], $controller->result);

        // a filter stops in the middle
        $controller = new FakeController('fake', Yii::$app, [
            'behaviors' => [
                'filter1' => Filter1::className(),
                'filter2' => Filter2::className(),
                'filter3' => Filter3::className(),
            ],
        ]);
        $this->assertNull($controller->result);
        $result = $controller->runAction('test');
        $this->assertNull($result);
        $this->assertEquals([1, 2], $controller->result);

        // the first filter stops
        $controller = new FakeController('fake', Yii::$app, [
            'behaviors' => [
                'filter2' => Filter2::className(),
                'filter1' => Filter1::className(),
                'filter3' => Filter3::className(),
            ],
        ]);
        $this->assertNull($controller->result);
        $result = $controller->runAction('test');
        $this->assertNull($result);
        $this->assertEquals([2], $controller->result);

        // the last filter stops
        $controller = new FakeController('fake', Yii::$app, [
            'behaviors' => [
                'filter1' => Filter1::className(),
                'filter3' => Filter3::className(),
                'filter2' => Filter2::className(),
            ],
        ]);
        $this->assertNull($controller->result);
        $result = $controller->runAction('test');
        $this->assertNull($result);
        $this->assertEquals([1, 3, 2], $controller->result);
    }


    public function actionFilterProvider()
    {
        return [
            [['class' => 'yii\filters\AccessControl', 'user' => 'yiiunit\framework\base\MockUser']],
            ['yii\filters\ContentNegotiator'],
            ['yii\filters\Cors'],
            ['yii\filters\HttpCache'],
            ['yii\filters\PageCache'],
            ['yii\filters\RateLimiter'],
        ];
    }

    /**
     * @dataProvider actionFilterProvider
     * @param string|array $filterClass
     */
    public function testActive($filterClass)
    {
        $this->mockWebApplication();

        /** @var $filter ActionFilter */
        $filter = Yii::createObject($filterClass);
        $reflection = new \ReflectionClass($filter);
        $method = $reflection->getMethod('isActive');
        $method->setAccessible(true);

        $controller = new \yii\web\Controller('test', Yii::$app);

        // active by default
        $this->assertTrue($method->invokeArgs($filter, [new Action('index', $controller)]));
        $this->assertTrue($method->invokeArgs($filter, [new Action('view', $controller)]));

        $filter->only = ['index'];
        $filter->except = [];
        $this->assertTrue($method->invokeArgs($filter, [new Action('index', $controller)]));
        $this->assertFalse($method->invokeArgs($filter, [new Action('view', $controller)]));

        $filter->only = ['index', 'view'];
        $filter->except = ['view'];
        $this->assertTrue($method->invokeArgs($filter, [new Action('index', $controller)]));
        $this->assertFalse($method->invokeArgs($filter, [new Action('view', $controller)]));

        $filter->only;
        $filter->except = ['view'];
        $this->assertTrue($method->invokeArgs($filter, [new Action('index', $controller)]));
        $this->assertFalse($method->invokeArgs($filter, [new Action('view', $controller)]));
    }

    /**
     * @depends testActive
     */
    public function testActiveWildcard()
    {
        $this->mockWebApplication();

        $filter = new ActionFilter();
        $reflection = new \ReflectionClass($filter);
        $method = $reflection->getMethod('isActive');
        $method->setAccessible(true);

        $controller = new \yii\web\Controller('test', Yii::$app);

        $filter->only = ['test/*'];
        $filter->except = [];
        $this->assertFalse($method->invokeArgs($filter, [new Action('index', $controller)]));
        $this->assertTrue($method->invokeArgs($filter, [new Action('test/index', $controller)]));

        $filter->only = [];
        $filter->except = ['test/*'];
        $this->assertTrue($method->invokeArgs($filter, [new Action('index', $controller)]));
        $this->assertFalse($method->invokeArgs($filter, [new Action('test/index', $controller)]));
    }
}

class FakeController extends Controller
{
    public $result;
    public $behaviors = [];

    public function behaviors()
    {
        return $this->behaviors;
    }

    public function actionTest()
    {
        return 'x';
    }
}

class Filter1 extends ActionFilter
{
    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        $action->controller->result[] = 1;
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function afterAction($action, $result)
    {
        return $result . '-1';
    }
}

class Filter2 extends ActionFilter
{
    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        $action->controller->result[] = 2;
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function afterAction($action, $result)
    {
        return $result . '-2';
    }
}

class Filter3 extends ActionFilter
{
    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        $action->controller->result[] = 3;
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function afterAction($action, $result)
    {
        return $result . '-3';
    }
}

class MockUser extends User
{
    public function init()
    {
        // do not call parent to avoid the need to mock configuration
    }
}
