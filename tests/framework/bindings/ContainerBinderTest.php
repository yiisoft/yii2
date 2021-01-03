<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\bindings;

use yii\base\InlineAction;
use yii\bindings\ActionParameterBinder;
use yii\bindings\binders\ContainerBinder;
use yii\bindings\BindingContext;
use yii\bindings\ModelBinderInterface;
use yii\console\Application;
use yiiunit\TestCase;

class ContainerBinderTest extends TestCase
{
    /**
     * @var ActionParameterBinder
     */
    private $parameterBinder;

    /**
     * @var ModelBinderInterface
     */
    private $modelBinder;

    /**
     * @var BindingContext
     */
    private $context = null;

    protected function setUp()
    {
        parent::setUp();

        $this->parameterBinder = new ActionParameterBinder([]);
        $this->modelBinder = new ContainerBinder();

        $this->mockWebApplication([
            'components' => [
            ],
        ]);
    }

    public function testContainerBinder()
    {
        if (PHP_VERSION_ID < 70100) {
            $this->markTestSkipped('Can not be tested on PHP < 7.1');
            return;
        }

        $module = new \yii\base\Module('fake', new Application(['id' => 'app',  'basePath' => __DIR__,]));
        $module->set(yii\web\Request::class, ['class' => yii\web\Request::class]);
        $controller = new ActionBindingController('binding', $module);

        $this->mockWebApplication(['controller' => $controller]);

        $action = new InlineAction("action", $controller, "actionTest");

        $result = $this->parameterBinder->bindActionParams($action, []);
        $args   = $result->arguments;

        $instance = $args["request"];

        $this->assertNotNull($instance);
        $this->assertInstanceOf("yii\web\Request", $instance);
    }
}
