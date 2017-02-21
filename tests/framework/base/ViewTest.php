<?php
namespace yiiunit\framework\base;

use Yii;
use yii\base\View;
use yii\caching\FileCache;
use yiiunit\TestCase;

/**
 * @group base
 */
class ViewTest extends TestCase
{
    public function testRenderDynamic()
    {
        Yii::$app->set('cache', new FileCache(['cachePath' => '@yiiunit/runtime/cache']));
        $view = new View();
        $this->assertEquals(1, $view->renderDynamic('return 1;'));
    }

    public function testRenderDynamic_DynamicPlaceholders()
    {
        Yii::$app->set('cache', new FileCache(['cachePath' => '@yiiunit/runtime/cache']));
        $statement = "return 1;";
        $view = new View();
        if ($view->beginCache(__FUNCTION__, ['duration' => 3])) {
            $view->renderDynamic($statement);
            $view->endCache();
        }
        $this->assertEquals([
            '<![CDATA[YII-DYNAMIC-0]]>' => $statement
        ], $view->dynamicPlaceholders);
    }

    public function testRenderDynamic_StatementWithThisVariable()
    {
        Yii::$app->set('cache', new FileCache(['cachePath' => '@yiiunit/runtime/cache']));
        $view = new View();
        $view->params['viewParam'] = 'dummy';
        $this->assertEquals($view->params['viewParam'], $view->renderDynamic('return $this->params["viewParam"];'));
    }

    public function testRenderDynamic_IncludingParams()
    {
        Yii::$app->set('cache', new FileCache(['cachePath' => '@yiiunit/runtime/cache']));
        $view = new View();
        $this->assertEquals('YiiFramework', $view->renderDynamic('return $a . $b;', [
            'a' => 'Yii',
            'b' => 'Framework',
        ]));
    }

    public function testRenderDynamic_IncludingParams_ThrowException()
    {
        Yii::$app->set('cache', new FileCache(['cachePath' => '@yiiunit/runtime/cache']));
        $view = new View();
        try {
            $view->renderDynamic('return $a;');
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        $this->assertEquals('Undefined variable: a', $message);
    }

    protected function setUp()
    {
        parent::setUp();
        $this->mockWebApplication();
    }
}
