<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\bindings;

use Yii;
use yii\base\InlineAction;
use yii\bindings\ActionParameterBinder;
use yii\bindings\binders\DataFilterBinder;
use yii\bindings\BindingContext;
use yii\bindings\ModelBinderInterface;
use yii\console\Application;
use yiiunit\framework\bindings\mocks\ActionBindingController;
use yiiunit\TestCase;

class DataFilterBinderTest extends TestCase
{
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
        $this->modelBinder = new DataFilterBinder();
        $this->parameterBinder = new ActionParameterBinder();

        $module = new \yii\base\Module('fake', new Application(['id' => 'app',  'basePath' => __DIR__,]));
        $module->set(yii\web\Request::class, ['class' => yii\web\Request::class]);
        $this->controller = new ActionBindingController('binding', $module);

        $this->mockWebApplication(['controller' => $this->controller]);
    }

    public function testDataFilter()
    {
        $action = new InlineAction("action", $this->controller, "actionDataFilter");

        $values = [
            "filter" => [
                "name" => "value"
            ],
        ];

        $_SERVER['REQUEST_METHOD'] = "POST";
        Yii::$app->request->setBodyParams($values);


        $result = $this->parameterBinder->bindActionParams($action, []);
        $args   = $result->arguments;

        /**
         * @var \yii\data\DataFilter
         */
        $instance = $args["model"];

        $this->assertNotNull($instance);
        $this->assertInstanceOf(\yii\data\DataFilter::class, $instance);
        $this->assertSame($values["filter"], $instance->getFilter());
    }

    public function testActiveDataFilter()
    {
        $action = new InlineAction("action", $this->controller, "actionActiveDataFilter");

        $values = [
            "filter" => [
                "name" => "value"
            ],
        ];

        $_SERVER['REQUEST_METHOD'] = "POST";
        Yii::$app->request->setBodyParams($values);


        $result = $this->parameterBinder->bindActionParams($action, []);
        $args   = $result->arguments;

        /**
         * @var \yii\data\ActiveDataFilter
         */
        $instance = $args["model"];

        $this->assertNotNull($instance);
        $this->assertInstanceOf(\yii\data\ActiveDataFilter::class, $instance);
        $this->assertSame($values["filter"], $instance->getFilter());
    }
}
