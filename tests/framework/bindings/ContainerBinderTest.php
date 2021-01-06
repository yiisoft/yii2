<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\bindings;

use yii\bindings\binders\ContainerBinder;

/**
 * @group bindings
 * @requires PHP >= 7.1
 */
class ContainerBinderTest extends BindingTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->modelBinder = new ContainerBinder();
    }

    public function testContainerBinder()
    {
        if (PHP_VERSION_ID < 70100) {
            $this->markTestSkipped('Can not be tested on PHP < 7.1');
            return;
        }

        $action = $this->getControllerAction("actionTest");

        $result = $this->parameterBinder->bindActionParams($action, []);
        $args   = $result->arguments;

        $instance = $args["request"];

        $this->assertNotNull($instance);
        $this->assertInstanceOf("yii\web\Request", $instance);
    }
}
