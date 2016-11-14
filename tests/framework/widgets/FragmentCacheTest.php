<?php

namespace yiiunit\framework\widgets;

use Yii;
use yii\caching\ArrayCache;
use yii\base\View;
use yii\widgets\Breadcrumbs;
use yii\widgets\FragmentCache;

/**
 * @group widgets
 * @group caching
 */
class FragmentCacheTest extends \yiiunit\TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockWebApplication();
        Yii::$app->set('cache', [
            'class' => ArrayCache::className(),
        ]);
    }

    public function testCacheEnabled()
    {
        $expectedLevel = ob_get_level();
        ob_start();
        ob_implicit_flush(false);

        $view = new View();
        $this->assertTrue($view->beginCache('test'));
        echo "cached fragment";
        $view->endCache();

        ob_start();
        ob_implicit_flush(false);
        $this->assertFalse($view->beginCache('test'));
        $this->assertEquals("cached fragment", ob_get_clean());

        ob_end_clean();
        $this->assertEquals($expectedLevel, ob_get_level(), 'Output buffer not closed correctly.');
    }

    public function testCacheDisabled1()
    {
        $expectedLevel = ob_get_level();
        ob_start();
        ob_implicit_flush(false);

        $view = new View();
        $this->assertTrue($view->beginCache('test', ['enabled' => false]));
        echo "cached fragment";
        $view->endCache();

        ob_start();
        ob_implicit_flush(false);
        $this->assertTrue($view->beginCache('test', ['enabled' => false]));
        echo "cached fragment";
        $view->endCache();
        $this->assertEquals("cached fragment", ob_get_clean());

        ob_end_clean();
        $this->assertEquals($expectedLevel, ob_get_level(), 'Output buffer not closed correctly.');
    }

    public function testCacheDisabled2()
    {
        $expectedLevel = ob_get_level();
        ob_start();
        ob_implicit_flush(false);

        $view = new View();
        $this->assertTrue($view->beginCache('test'));
        echo "cached fragment";
        $view->endCache();

        ob_start();
        ob_implicit_flush(false);
        $this->assertTrue($view->beginCache('test', ['enabled' => false]));
        echo "cached fragment other";
        $view->endCache();
        $this->assertEquals("cached fragment other", ob_get_clean());

        ob_end_clean();
        $this->assertEquals($expectedLevel, ob_get_level(), 'Output buffer not closed correctly.');
    }

    public function testDynamicContentScalar()
    {
        $expectedLevel = ob_get_level();
        $view = new View();
        $placeholders = [
            'place' => 'dynamic'
        ];

        ob_start();
        ob_implicit_flush(false);
        $this->assertTrue($view->beginCache('test', ['placeholders' => $placeholders]));

        ob_start();
        ob_implicit_flush(false);
        echo "cached ";
        echo $view->renderDynamic('place');
        echo " fragment";
        $this->assertEquals("cached " . FragmentCache::placeholderMarker('place') . " fragment", $rawContent = ob_get_clean());

        echo $rawContent;
        $view->endCache();
        $this->assertEquals("cached dynamic fragment", ob_get_clean());

        ob_start();
        ob_implicit_flush(false);
        $this->assertFalse($view->beginCache('test', ['placeholders' => $placeholders]));
        $this->assertEquals("cached dynamic fragment", ob_get_clean());

        $this->assertEquals($expectedLevel, ob_get_level(), 'Output buffer not closed correctly.');
    }

    public function testDynamicContentClosure()
    {
        $expectedLevel = ob_get_level();
        $view = new View();
        $placeholders = [
            'place' => function () {
                    return 'dynamic';
                }
        ];

        ob_start();
        ob_implicit_flush(false);
        $this->assertTrue($view->beginCache('test', ['placeholders' => $placeholders]));

        ob_start();
        ob_implicit_flush(false);
        echo "cached ";
        echo $view->renderDynamic('place');
        echo " fragment";
        $this->assertEquals("cached " . FragmentCache::placeholderMarker('place') . " fragment", $rawContent = ob_get_clean());

        echo $rawContent;
        $view->endCache();
        $this->assertEquals("cached dynamic fragment", ob_get_clean());

        ob_start();
        ob_implicit_flush(false);
        $this->assertFalse($view->beginCache('test', ['placeholders' => $placeholders]));
        $this->assertEquals("cached dynamic fragment", ob_get_clean());

        $this->assertEquals($expectedLevel, ob_get_level(), 'Output buffer not closed correctly.');
    }
}
