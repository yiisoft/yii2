<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\bindings;

use yiiunit\framework\bindings\mocks\Point;

class ClassTypeBinderTest extends BindingTestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    public function testActionPoint()
    {
        $action = $this->getControllerAction("actionPoint");
        $result = $this->parameterBinder->bindActionParams($action, [
            "model" => [
                "x" => "25",
                "y" => "35"
            ]
        ]);

        $instance = $result->arguments["model"];

        $this->assertNotNull($instance);
        $this->assertInstanceOf(Point::class, $instance);
        $this->assertSame(25, $instance->x);
        $this->assertSame(35, $instance->y);
    }
}
