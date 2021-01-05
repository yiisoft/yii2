<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\bindings;

use yiiunit\framework\bindings\mocks\Circle;
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

    public function testActionCircle()
    {
        $action = $this->getControllerAction("actionCircle");
        $result = $this->parameterBinder->bindActionParams($action, [
            "model" => [
                "center" => [
                    "x" => "25",
                    "y" => "35"
                ],
                "radius" => "50",
                "color" => "purple"
            ]
        ]);

        $instance = $result->arguments["model"];

        $this->assertNotNull($instance);
        $this->assertInstanceOf(Circle::class, $instance);
        $this->assertInstanceOf(Point::class,  $instance->center);
        $this->assertSame(25, $instance->center->x);
        $this->assertSame(35, $instance->center->y);
        $this->assertSame(50.0, $instance->radius);
        $this->assertSame("purple", $instance->color);
    }

    public function testActionComposite()
    {
        $action = $this->getControllerAction("actionComposite");
        $result = $this->parameterBinder->bindActionParams($action, [
            "model" => [
                "circle" => [
                    "center" => [
                        "x" => "25",
                        "y" => "35"
                    ],
                    "radius" => "50",
                    "color" => "purple"
                ],
                "filter" => [
                    "name" => "value"
                ]
            ]
        ]);

        $instance = $result->arguments["model"];
        $circle = $instance->circle;
        $filter = $instance->filter;

        $this->assertNotNull($instance);
        $this->assertNotNull($circle);
        $this->assertNotNull($filter);
        $this->assertInstanceOf(\yii\web\Request::class, $instance->request);
        $this->assertInstanceOf(\yii\data\ActiveDataFilter::class, $filter);
        $this->assertInstanceOf(Circle::class, $circle);
        $this->assertInstanceOf(Point::class,  $circle->center);
        $this->assertSame(25, $circle->center->x);
        $this->assertSame(35, $circle->center->y);
        $this->assertSame(50.0, $circle->radius);
        $this->assertSame("purple", $circle->color);

        //TODO: Test that filter is populated
    }
}
