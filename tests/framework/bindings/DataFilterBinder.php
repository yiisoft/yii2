<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\bindings;

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

    }

    public function testActiveDataFilter()
    {
    }
}
