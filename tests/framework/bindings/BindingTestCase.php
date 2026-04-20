<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\bindings;

use Yii;
use yii\base\Module;
use yii\web\Request;
use yii\base\InlineAction;
use yii\bindings\ActionParameterBinder;
use yii\bindings\BindingContext;
use yii\bindings\ModelBinderInterface;
use yii\console\Application;
use yiiunit\framework\bindings\mocks\ActionBindingController;
use yiiunit\TestCase;

class BindingTestCase extends TestCase
{
    /**
     * @var ActionParameterBinder
     */
    protected $parameterBinder;

    /**
     * @var ModelBinderInterface
     */
    protected $modelBinder;

    /**
     * @var BindingContext
     */
    protected $context = null;

    /**
     * @var ActionBindingController
     */
    protected $controller = null;

    protected function setBodyParams($values)
    {
        $_SERVER['REQUEST_METHOD'] = "POST";
        Yii::$app->request->setBodyParams($values);
    }

    protected function setUp()
    {
        parent::setUp();
        if (PHP_VERSION_ID < 70100) {
            $this->markTestSkipped("Can not be tested on PHP < 7.1");
        }

        $this->parameterBinder = new ActionParameterBinder();
        $module = new Module('fake', new Application(['id' => 'app',  'basePath' => __DIR__,]));
        $module->set(Request::className(), ['class' => Request::className()]);
        $this->controller = new ActionBindingController('binding', $module);
        $this->mockWebApplication(['controller' => $this->controller]);
    }

    protected function getControllerAction($actionMethod)
    {
        return new InlineAction("action", $this->controller, $actionMethod);
    }

}